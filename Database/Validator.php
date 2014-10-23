<?php

namespace TenUp\Exodus\Database;

use TenUp\Exodus\Schema\Base_Schema;

/**
 * Class Validator
 * @package TenUp\Exodus\Database
 */
class Validator {

	/**
	 * @var array indexes of content items to check
	 */
	public $check = array();

	/**
	 * Build out the random list of post to check.
	 *
	 * @param int $percent 1-100
	 * @param int $total   number of content items imported
	 */
	public function setup( $percent, $total ) {
		$item_count = round( $total * ( $percent / 100 ) );
		if ( $total > 1 ) {
			$items       = range( 0, ( $total - 1 ) );
			$this->check = array_rand( $items, $item_count );
		} else {
			$this->check = array( 0 );
		}
	}

	/**
	 * Check to see if the imported content matches whats in the database.
	 *
	 * @param stdclass $content import content object
	 * @param int      $id      imported post id
	 *
	 * @return bool
	 */
	public function compare( $content, $id ) {
		$post = get_post( $id );

		foreach ( $content as $key => $value ) {
			if ( isset( $post->$key ) ) {
				if ( $this->strip_image_paths( $value, $key ) !== $this->strip_image_paths( $content->$key, $key ) ) {
					return $id;
				}
			} else {
				if ( 'meta_data' === $key && ! $this->check_post_meta( $value, $id ) ) {
					return $id;
				} elseif ( 'taxonomy' === $key && ! $this->check_post_terms( $value, $id ) ) {
					return $id;
				}
			}
		}

		return true;
	}

	/**
	 * Check to see if the id passes is in the $check array.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function should_compare( $id ) {
		return in_array( $id, $this->check ) ? true : false;
	}

	/**
	 * Check to see if the imported meta data matches whats in the database.
	 *
	 * @param array $meta_data
	 * @param int $id
	 *
	 * @return bool
	 */
	protected function check_post_meta( $meta_data, $id ) {
		$meta = get_post_meta( $id );
		if ( $meta ) {
			$errors = 0;
			foreach ( $meta_data as $meta_key => $meta_value ) {
				if ( isset( $meta[ $meta_key ] ) && $meta[ $meta_key ][0] === $meta_value ) {
					continue;
				} else {
					$errors ++;
				}
			}
			if ( $errors > 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check to see if imported taxonomy matches whats in the database.
	 *
	 * @param array $taxonomies
	 * @param int $id
	 *
	 * @return bool
	 */
	protected function check_post_terms( $taxonomies, $id ) {
		$errors = 0;
		foreach ( $taxonomies as $tax_key => $terms ) {
			if ( ! has_term( $terms, $tax_key, $id ) ) {
				$errors ++;
			}
			if ( $errors > 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Empty out the src attribute in order to take into consideration
	 * that the url of the images imported will not match.
	 *
	 * @param $content
	 * @param $key
	 *
	 * @return mixed
	 */
	protected function strip_image_paths( $content, $key ) {
		return ( 'post_content' === $key ) ? $content : preg_replace( "~src=[']([^']+)[']~e", '', $content );
	}

}