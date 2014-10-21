<?php

namespace TenUp\Exodus\Schema;
use SebastianBergmann\Exporter\Exception;


/**
 * Class Builder
 * @package TenUp\Exodus\Schema
 */
class Builder {

	/**
	 * @var array schema data
	 */
	protected $data = array();

	/**
	 * @var string post type that schema is getting built for
	 */
	protected $post_type_key = 'post';

	/**
	 * Initiate each mapped field method.
	 *
	 * @param $callback
	 */
	function __construct( $callback ) {
		try {
			$callback( $this );
		} catch ( Exception $e ) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

	/**
	 * Set the schema post type.
	 *
	 * @param $key
	 */
	public function post_type( $key ) {
		$this->data['post_type'] = $key;
		$this->post_type_key     = $key;
	}

	/**
	 * Set the schema post content.
	 *
	 * @param $key
	 */
	public function post_content( $key ) {
		$this->data['post_content'] = $key;
	}

	/**
	 * Set the schema post_title.
	 *
	 * @param $key
	 */
	public function post_title( $key ) {
		$this->data['post_title'] = $key;
	}

	/**
	 * Set the schema post author.
	 *
	 * @param $author
	 */
	public function post_author( $author ) {
		$this->data['post_author'] = $author;
	}

	/**
	 * Set the schema post excerpt.
	 *
	 * @param $key
	 */
	public function post_excerpt( $key ) {
		$this->data['post_excerpt'] = $key;
	}

	/**
	 * Set the schema post date.
	 *
	 * @param $key
	 */
	public function post_date( $key ) {
		$this->data['post_date'] = $key;
	}

	/**
	 * Set the schema post date GMT.
	 *
	 * @param $key
	 */
	public function post_date_gmt( $key ) {
		$this->data['post_date_gmt'] = $key;
	}

	/**
	 * Add meta data to the schema.
	 *
	 * @param $key
	 * @param $key
	 */
	public function meta_data( $meta_key, $key ) {
		$this->data['meta_data'][ $meta_key ] = $key;
	}

	/**
	 * Add taxonomy to the schema.
	 *
	 * @param $taxonomy
	 * @param $key
	 */
	public function taxonomy( $taxonomy, $key ) {
		$this->data['taxonomy'][ $taxonomy ] = $key;
	}

	/**
	 * Return built schema.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Return post type the schema was for.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type_key;
	}

	/**
	 * Capture undeclared methods
	 *
	 * @param $name
	 * @param $arguments
	 */
	public function __call( $name, $arguments ) {
		throw new \Exception( 'The ' . $name . ' method does not exist.' );
	}
}