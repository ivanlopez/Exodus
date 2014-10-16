<?php

namespace TenUp\Exodus\Migrator\Module;

abstract class Content_Import {

	protected function post( $data, $force ) {
		global $wpdb;

		$migration_hash = md5( $data->title . $data->cotent );

		// grab the existing post ID (if it exists).
		$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'migration_import_id' AND meta_value = '" . $migration_hash . "'" );

		if ( ! $force && $wp_id ) {
			return null;
		}

		$excerpt       = isset( $data->excerp ) ? '' : $data->excerp;
		$date          = isset( $data->date ) ? date( 'Y-m-d H:i:s', strtotime( 'now' ) ) : $data->date;
		$date_gmt      = isset( $data->date_created ) ? $date : $data->date_created;
		$post_modified = isset( $data->date_updated ) ? $date : $data->date_updated;

		$post = array(
			'post_status'   => 'publish',
			'post_type'     => $data->type,
			'post_title'    => $data->title,
			'post_content'  => $data->content,
			'post_author'   => $this->user( $data->author ),
			'post_excerpt'  => $excerpt,
			'post_date'     => $date,
			'post_date_gmt' => $date_gmt,
			'post_modified' => $post_modified,
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
		$updated_post['post_content'] = $this->media( $data->content, $wp_id );
		wp_update_post( $updated_post );
		update_post_meta( $wp_id, 'migration_import_id', $migration_hash );

		//Create post meta fields
		if ( isset( $data->meta ) ) {
			$this->post_meta( $data->meta, $wp_id );
		}

		if ( isset( $data->terms ) ) {
			$this->taxonomy( $data->terms, $wp_id );
		}

		return true;
	}

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

	protected function taxonomy( $data, $post_id ) {
		foreach ( $data->terms as $taxonomy ) {
			if ( isset( $taxonomy['type'] ) ) {
				$term_ids = array();
				foreach ( $taxonomy['terms'] as $term ) {

					$term_args = array();
					$term_name = sanitize_term_field( 'name', $term['name'], 0, $taxonomy['type'], 'db' );

					if ( isset( $term['parent'] ) ) {
						$parent_term_name = sanitize_term_field( 'name', $term['parent'], 0, $taxonomy['type'], 'db' );
						if ( $parent_term = term_exists( $term['parent'], $taxonomy['type'] ) ) {
							$term_args['parent'] = $parent_term['term_id'];
						} else {
							$term_args['parent'] = wp_insert_term( $parent_term_name, $taxonomy['type'] );
						}
					}

					if ( $taxonomy_term = term_exists( $term_name, $taxonomy['type'] ) ) {
						$term_ids[] = $taxonomy_term['term_id'];
					} else {
						$term_ids[] = wp_insert_term( $term_name, $taxonomy['type'], $term_args );
					}
				}

				wp_set_post_terms( $post_id, $term_ids, $taxonomy['type'], true );
			}
		}
	}

	protected function user( $user ) {

		if ( $user_id = email_exists( $user->email ) ) {
			return $user_id;
		} else if ( $user_id = username_exists( sanitize_user( $user->author ) ) ) {
			return $user_id;
		} else {
			$userdata = array(
				'user_login'    => sanitize_user( $user->author ),
				'user_pass'     => wp_generate_password(),
				'user_nicename' => $user->slug,
				'nickname'      => $user->author,
				'display_name'  => $user->author,
				'user_email'    => $user->email,
				'description'   => $user->description,
				'user_url'      => $user->url
			);

			$user_id = wp_insert_user( $userdata );

			if ( ! is_wp_error( $user_id ) ) {
				return $user_id;
			}
		}

		WP_CLI::line( "Error: $user->author could not be created." );

		return 1;
	}

	protected function user_meta( $meta_fields, $user_id ) {
		if ( is_array( $meta_fields ) ) {
			foreach ( $meta_fields as $key => $value ) {
				update_user_meta( $user_id, $key, $value );
			}
		}
	}

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
						WP_CLI::line( "Error: $path not changed in post content." );
					}
				}
			}
		}

		return $content;
	}

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
				WP_CLI::line( "Error: " . $id->get_error_message() );
				WP_CLI::line( "Filename: $old_filename" );
			} else {
				@unlink( $file_array['tmp_name'] );

				return $id;
			}
		} else {
			@unlink( $tmp );
			WP_CLI::line( "Error: " . $filename . " not added." );
		}

		return false;
	}
}