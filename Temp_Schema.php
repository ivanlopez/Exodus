<?php

namespace TenUp\Exodus;

use TenUp\Exodus\Schema\Base_Schema;
use TenUp\Exodus\Schema\Map;

class Temp_Schema extends Base_Schema {

	public $type = 'json';

	public $site = 8;

	public $iterator = 'response.posts';

	public function build() {

		Map::factory()->create( 'post', function ( $data ) {
			$data->post_title( 'title' );
			$data->post_content( 'body' );
			$data->post_date( 'timestamp' );
			$data->meta_data( 'post_url', 'post_url' );
			$data->meta_data( 'post_id', 'id' );
			$data->taxonomy( 'post_tag', 'tags' );
		} );

		return Map::factory()->schema();
	}

	public function keys(){
		return Map::factory()->keys();
	}

}