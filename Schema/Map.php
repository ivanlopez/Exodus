<?php
/**
 * Created by PhpStorm.
 * User: ivanlopez
 * Date: 10/16/14
 * Time: 3:25 PM
 */

namespace TenUp\Exodus\Schema;

class Map {

	protected $map = array();

	protected $post_type_keys;

	public static function factory() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	public function create( $post, $callback ) {
		$data_builder = new Builder( $callback );
		$this->map[ $post ] = $data_builder->get_data();
		$this->post_type_keys[ $data_builder->get_post_type() ] = $post;
	}

	public function schema(){
		return $this->map;
	}

	public function get_post_type_keys(){
		return $this->post_type_keys;
	}

}