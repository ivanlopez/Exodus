<?php

namespace TenUp\Exodus\Schema;


class Builder {

	protected $data = array();

	protected $post_type_key = 'post';

	function __construct( $callback ) {
		$callback( $this );
	}

	public function post_type( $key ) {
		$this->data['post_type'] = $key;
		$this->post_type = $key;
	}

	public function post_content( $key ) {
		$this->data['post_content'] = $key;
	}

	public function post_title( $key ) {
		$this->data['post_title'] = $key;
	}

	public function post_author( $author ) {
		$this->data['post_author'] = $author;
	}

	public function post_excerpt( $key ) {
		$this->data['post_excerpt'] = $key;
	}

	public function post_date( $key ) {
		$this->data['post_date'] = $key;
	}

	public function post_date_gmt( $key ) {
		$this->data['post_date_gmt'] = $key;
	}

	public function meta_data( $key, $key ) {
		$this->data['meta_data'][ $key ] = $key;
	}

	public function taxonomy( $taxonomy, $key ) {
		$this->data['taxonomy'][ $taxonomy ] = $key;
	}

	public function get_data() {
		return $this->data;
	}

	public function get_post_type(){
		return $this->post_type_key;
	}
}