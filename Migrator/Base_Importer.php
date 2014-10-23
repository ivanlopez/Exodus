<?php

namespace TenUp\Exodus\Migrator;

/**
 * Class Base_Importer
 * @package TenUp\Exodus\Migrator
 */
abstract class Base_Importer {

	/**
	 * Insert a new post.
	 *
	 * @param $data
	 * @param $force
	 *
	 * @return int|\WP_Error
	 */
	public function insert_post( $data, $force = false, $pretend = false ) {
		global $wpdb;

		if ( $pretend ) {
			return 1;
		}

		$excerpt        = isset( $data->post_excerpt ) ? $data->post_excerpt : '';
		$date           = isset( $data->post_date ) ? $data->post_date : date( 'Y-m-d H:i:s', strtotime( 'now' ) );
		$date           = is_int( $date ) ? date( 'Y-m-d H:i:s', $date ) : date( 'Y-m-d H:i:s', strtotime( $date ) );
		$date_gmt       = isset( $data->post_date_gmt ) ? date( 'Y-m-d H:i:s', $data->post_date_gmt ) : $date;
		$date_gmt       = is_int( $date_gmt ) ? date( 'Y-m-d H:i:s', $date_gmt ) : date( 'Y-m-d H:i:s', strtotime( $date_gmt ) );
		$post_author    = ( isset( $data->post_author ) && ! empty( $data->post_author ) ) ? $this->user( $data->post_author ) : 1;
		$migration_hash = md5( $data->post_title . $date );

		// grab the existing post ID (if it exists).
		$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'migration_import_id' AND meta_value = '" . $migration_hash . "'" );

		if ( ! $force && $wp_id ) {
			return true;
		}

		$post = array(
			'post_status'   => 'publish',
			'post_type'     => $data->post_type,
			'post_title'    => $data->post_title,
			'post_content'  => $data->post_content,
			'post_author'   => $post_author,
			'post_excerpt'  => $excerpt,
			'post_date'     => $date,
			'post_date_gmt' => $date_gmt,
		);

		if ( $wp_id ) {
			$post['ID'] = $wp_id;
		}

		$wp_id = wp_insert_post( $post );

		if ( is_wp_error( $wp_id ) ) {
			return false;
		}

		$updated_post = array( 'ID' => $wp_id );

		//Download images found in the post_content and update post_content
		$updated_post['post_content'] = $this->media( $data->post_content, $wp_id );
		wp_update_post( $updated_post );
		update_post_meta( $wp_id, 'migration_import_id', $migration_hash );

		//Create post meta fields
		if ( isset( $data->meta_data ) ) {
			$this->post_meta( $data->meta_data, $wp_id );
		}

		if ( isset( $data->taxonomy ) ) {
			$this->taxonomy( $data->taxonomy, $wp_id );
		}

		return $wp_id;
	}

	/**
	 * Insert meta data for the current post.
	 *
	 * @param $meta_fields
	 * @param $post_id
	 */
	protected function post_meta( $meta_fields, $post_id ) {
		if ( is_array( $meta_fields ) ) {
			foreach ( $meta_fields as $key => $value ) {
				# TODO in the future check to see if $value is a file path that needs to be uploaded
				if ( 'featured_image' === $key ) {
					$id = $this->upload_media( $value, $post_id );
					if ( is_wp_error( $id ) ) {
						return false;
					} else {
						set_post_thumbnail( $post_id, $id );

						return true;
					}
				} else {
					update_post_meta( $post_id, $key, $value );
				}
			}
		}
	}

	/**
	 * Insert taxonomy for current post and map it to this post. If taxonomy
	 * exist just map the taxonomy to current post.
	 *
	 * @param $data
	 * @param $post_id
	 */
	protected function taxonomy( $data, $post_id ) {
		foreach ( $data as $key => $taxonomy ) {
			$term_ids = array();
			foreach ( $taxonomy as $term ) {

				$term_args = array();
				$term_name = sanitize_term_field( 'name', $term, 0, $key, 'db' );

				# TODO Allow for taxonomy parenting
				/*if ( isset( $term['parent'] ) ) {
					$parent_term_name = sanitize_term_field( 'name', $term['parent'], 0, $key, 'db' );
					if ( $parent_term = term_exists( $term['parent'], $key ) ) {
						$term_args['parent'] = $parent_term['term_id'];
					} else {
						$term_args['parent'] = wp_insert_term( $parent_term_name, $key );
					}
				}*/

				if ( $taxonomy_term = term_exists( $term_name, $key ) ) {
					$term_ids[] = $taxonomy_term['term_id'];
				} else {
					$taxonomy_term = wp_insert_term( $term_name, $key, $term_args );
					$term_ids[]    = $taxonomy_term['term_id'];
				}
			}

			wp_set_object_terms( $post_id, $term_ids, $key, true );
		}
	}

	/**
	 * Insert new user for current post. If user already exist just return user id.
	 *
	 * @param $user
	 *
	 * @return bool|int|null|\WP_Error
	 */
	protected function user( $user ) {
		if ( $user_id = email_exists( $user->user_email ) ) {
			return $user_id;
		} else if ( $user_id = username_exists( sanitize_user( $user->user_login ) ) ) {
			return $user_id;
		} else {
			$user_data = array(
				'user_login'    => sanitize_user( $user->user_login ),
				'user_pass'     => wp_generate_password(),
				'user_nicename' => $user->user_login,
				'nickname'      => $user->user_login,
				'display_name'  => $user->user_login,
				'first_name'    => isset( $user->first_name ) ? $user->first_name : '',
				'last_name'     => isset( $user->last_name ) ? $user->last_name : '',
				'user_email'    => isset( $user->user_email ) ? $user->user_email : '',
			);

			$user_id = wp_insert_user( $user_data );

			if ( ! is_wp_error( $user_id ) ) {
				return $user_id;
			}
		}

		\WP_CLI::line( "Error: $user->user_login could not be created." );

		return 1;
	}

	/**
	 * Insert user meta data.
	 *
	 * @param $meta_fields
	 * @param $user_id
	 */
	protected function user_meta( $meta_fields, $user_id ) {
		if ( is_array( $meta_fields ) ) {
			foreach ( $meta_fields as $key => $value ) {
				update_user_meta( $user_id, $key, $value );
			}
		}
	}

	/**
	 * Scrape post content for any image and send them to
	 * be uploaded into WordPress and replace the paths in
	 * the post content.
	 *
	 * @param $content
	 * @param $post_id
	 *
	 * @return mixed
	 */
	protected function media( $content, $post_id ) {
		preg_match_all( '#<img(.*?)src="(.*?)"(.*?)>#', $content, $matches, PREG_SET_ORDER );

		if ( is_array( $matches ) ) {
			foreach ( $matches as $match ) {

				$path = $match[2];
				$id   = $this->upload_media( $path, $post_id );

				if ( $id ) {
					$src = wp_get_attachment_url( $id );

					if ( $src ) {
						$content = str_replace( $path, $src, $content );
					} else {
						\WP_CLI::line( "Error: $path not changed in post content." );
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Upload images to the uploads folder.
	 *
	 * @param $path
	 * @param $post_id
	 *
	 * @return bool
	 */
	protected function upload_media( $path, $post_id ) {
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$old_filename = '';

		$filename = str_replace( '\\', '/', $path );
		$filename = urldecode( $filename ); // for filenames with spaces
		$filename = str_replace( ' ', '%20', $filename );
		$filename = str_replace( '&amp;', '&', $filename );
		$filename = str_replace( '&mdash;', '—', $filename );

		if ( preg_match( '/^http/', $filename ) || preg_match( '/^www/', $filename ) ) {
			$old_filename = $filename;
		}

		$tmp = download_url( $old_filename );

		preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|Jpeg|JPEG|gif|GIF|png|PNG)/', $filename, $matches );

		// make sure we have a match.  This won't be set for PDFs and .docs
		if ( $matches && isset( $matches[0] ) ) {
			$name                   = str_replace( '%20', ' ', basename( $matches[0] ) );
			$file_array['name']     = $name;
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, null, array() );

			// If error storing permanently, unlink
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
				\WP_CLI::line( "Error: " . $id->get_error_message() );
				\WP_CLI::line( "Filename: $old_filename" );
			} else {
				@unlink( $file_array['tmp_name'] );

				return $id;
			}

		} else {
			@unlink( $tmp );
			\WP_CLI::line( "Error: " . $filename . " not added." );
		}

		return false;
	}
}