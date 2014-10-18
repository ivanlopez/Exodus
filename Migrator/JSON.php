<?php

namespace TenUp\Exodus\Migrator;

use TenUp\Exodus\Migrator\Base_Importer;
use TenUp\Exodus\Schema\Base_Schema;

final class JSON extends Base_Importer  implements Migrator{

	protected $data;

	protected $schema;

	protected $force;

	protected $schema_map;

	function __construct( $data, Base_Schema $schema, $force ) {
		$this->data = $data;
		$this->schema = $schema;
		$this->force = $force;
		$this->schema_map = $this->schema->build();
	}

	public function import(){
		if( isset( $this->schema->site ) ){
			switch_to_blog( (int) $this->schema->site );
		}
		$this->data = $this->update_iterator_path( $this->data );
		$total = count( $this->data );

		if( $total > 0){
			$notify = new \cli\progress\Bar( "There's $total total post being imported.", $total );

			foreach( $this->data as $content ){
				$post = $this->build_post_object( $content );
				if( $this->insert_post( $post, $this->force ) ){
					$notify->tick();
				}
			}
			$notify->finish();
		}
	}

	public function build_post_object( $content ){
		$post = new \stdClass;

		if( count( $this->schema->post_type_keys ) > 1 ){
				$post_type_key = $content[ $this->schema_map[0]['post_type'] ];
				$schema = $this->schema_map[ $this->schema->post_type_keys[ $post_type_key ] ];
		} else {
			$schema = reset( $this->schema_map );
			$post->post_type = 'post';
		}

		foreach( $schema as $content_key => $schema_key ){
			if( is_array( $schema_key ) ){
				$post->$content_key = array();
				$temp_schema_data = array();
				foreach( $schema_key as $sub_key => $sub_value ){
					$temp_schema_data[$sub_key] = $content->$sub_value;
				}
				$post->$content_key = $temp_schema_data;
			} else {
				$post->$content_key = $content->$schema_key;
			}
		}

		return $post;
	}

	protected function update_iterator_path( $data ){
		if( isset( $this->schema->iterator ) ){
			$children = explode( '.',  $this->schema->iterator);
			foreach( $children as $child ){
				$data = $data->$child;
			}
		}
		return $data;
	}

}