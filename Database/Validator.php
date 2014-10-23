<?php

namespace TenUp\Exodus\Database;

use TenUp\Exodus\Schema\Base_Schema;

class Validator {

	public $check = array();

	public function setup( $percent, $total ) {
		$item_count = round( $total * ( $percent / 100 ) );
		if( $total > 1){
			$items  = range( 0, ( $total - 1 ) );
			$this->check = array_rand( $items, $item_count );
		} else {
			$this->check = array( 0 );
		}


	}

	public function compare( $content, $id ) {
		$post = get_post( $id );

		foreach ( $content as $key => $value ) {
			if ( isset( $post->$key ) ) {
				if ( $this->strip_image_paths( $value, $key ) !== $this->strip_image_paths( $content->$key, $key ) ) {
					return $id;
				}
			} else {
				if( 'meta_data' === $key && ! $this->check_post_meta( $value, $id ) ) {
					return $id;
				} elseif( 'taxonomy' === $key && ! $this->check_post_terms( $value, $id ) ){
					return $id;
				}
			}
		}

		return true;
	}

	public function should_compare( $id ) {
		return in_array( $id, $this->check ) ? true : false;
	}

	protected function check_post_meta( $meta_data, $id ){
		$meta = get_post_meta( $id );
		if( $meta ){
			$errors = 0;
			foreach( $meta_data as $meta_key => $meta_value ){
				if( isset($meta[ $meta_key ]) && $meta[ $meta_key ][0] === $meta_value ){
					continue;
				} else {
					$errors++;
				}
			}
			if( $errors > 0){
				return false;
			}
		}

		return true;
	}

	protected function check_post_terms( $taxonomies, $id ){
		$errors = 0;
		foreach ( $taxonomies as $tax_key => $terms ) {
			if( ! has_term( $terms, $tax_key, $id ) ){
				$errors++;
			}
			if( $errors > 0){
				return false;
			}
		}

		return true;
	}

	protected function strip_image_paths( $content, $key ) {
		return ( 'post_content' === $key ) ? $content : preg_replace( "~src=[']([^']+)[']~e", '', $content );
	}

}