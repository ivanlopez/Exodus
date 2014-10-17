<?php

namespace TenUp\Exodus\Schema;


class Builder {

	protected $data = array();

	function __construct( $callback ) {
		$callback( $this );
	}

	public function post_content( $path ) {
		$this->data['post_content'] = $path;
	}

	public function post_title( $path ) {
		$this->data['post_title'] = $path;
	}

	public function post_author( $author ) {
		if( is_array( $author ) ){
			$this->data['post_author'] = $author;
		}
	}

	public function post_excerpt( $path ) {
		$this->data['post_excerpt'] = $path;
	}

	public function post_date( $path ) {
		$this->data['post_date'] = $path;
	}

	public function post_date_gmt( $path ) {
		$this->data['post_date_gmt'] = $path;
	}

	public function meta_data( $key, $path ) {
		$this->data['meta_data'][ $key ] = $path;
	}

	public function taxonomy( $taxonomy, $path ) {
		$this->data['taxonomy'][ $taxonomy ] = $path;
	}

	public function get_data() {
		return $this->data;
	}
}