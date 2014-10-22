<?php

namespace TenUp\Exodus\Migrator\Parsers;

use TenUp\Exodus\Schema\Base_Schema;

/**
 * Class JSON
 * @package TenUp\Exodus\Migrator\Parsers
 */
final class JSON extends Base_Parser {

	/**
	 * Setup JSON dependencies.
	 *
	 * @param array       $data
	 * @param Base_Schema $schema
	 */
	function __construct( $data, Base_Schema $schema ) {
		$this->schema      = $schema;
		$this->schema_map  = $this->schema->build();
		$this->schema_keys = $this->schema->keys();
		$this->build_data( json_decode( $data ) );
	}

	/**
	 * Prepares the raw imported data by running it against the
	 * schema maps and populating the $content array with import post objects.
	 *
	 * @param array $data
	 */
	protected function build_data( $data ) {
		$data        = $this->update_iterator_path( $data );
		$this->data  = $data;

		if ( $data > 0 ) {
			foreach ( $data as $content ) {
				$this->content[] = $this->build_post_object( $content );
			}
		}
	}

	/**
	 * Converts a single raw content piece into a import post object by
	 * parsing the raw content with the schema map.
	 *
	 * @param $content
	 *
	 * @return \stdClass
	 */
	public function build_post_object( $content ) {
		$post = new \stdClass;

		if ( count( $this->schema->keys ) > 1 ) {
			$post_type_key = $content[ $this->schema_map[0]['post_type'] ];
			$schema        = $this->schema_map[ $this->schema_keys[ $post_type_key ] ];
		} else {
			$schema          = reset( $this->schema_map );
			$post->post_type = 'post';
		}

		foreach ( $schema as $content_key => $schema_key ) {
			if ( is_array( $schema_key ) ) {
				$post->$content_key = array();
				$temp_schema_data   = array();
				foreach ( $schema_key as $sub_key => $sub_value ) {
					$temp_schema_data[ $sub_key ] = $content->$sub_value;
				}
				$post->$content_key = $temp_schema_data;
			} else {
				if( 'post_author' === $content_key ){
					$post->$content_key = $this->setup_author( $content->$schema_key );
				} else {
					$post->$content_key = $content->$schema_key;
				}
			}
		}

		return $post;
	}

	/**
	 * Update the data to point to the post iterate based of the
	 * schema iterator parameter.
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function update_iterator_path( $data ) {
		if ( isset( $this->schema->iterator ) ) {
			$children = explode( '.', $this->schema->iterator );
			foreach ( $children as $child ) {
				$data = $data->$child;
			}
		}

		return $data;
	}

}