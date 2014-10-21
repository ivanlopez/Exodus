<?php

namespace TenUp\Exodus\Schema;

/**
 * Class Map
 * @package TenUp\Exodus\Schema
 */
class Map {

	/**
	 * @var array list of post type maps
	 */
	protected $map = array();

	/**
	 * @var array list of post type mapped keys
	 */
	protected $post_type_keys = array();

	/**
	 * Return singleton instance of the class.
	 *
	 * @return Map
	 */
	public static function factory() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Create a new schema map fo the provided post type.
	 *
	 * @param string   $post
	 * @param function $callback
	 */
	public function create( $post, $callback ) {
		$data_builder                                           = new Builder( $callback );
		$this->map[ $post ]                                     = $data_builder->get_data();
		$this->post_type_keys[ $data_builder->get_post_type() ] = $post;
	}

	/**
	 * Return all of the schema maps.
	 *
	 * @return array
	 */
	public function schema() {
		return $this->map;
	}

	/**
	 * Return all of the post type keys.
	 *
	 * @return array
	 */
	public function keys() {
		return $this->post_type_keys;
	}

}