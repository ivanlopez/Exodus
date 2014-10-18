<?php

namespace TenUp\Exodus;

use TenUp\Exodus\Schema\Base_Schema;
use TenUp\Exodus\Schema\Map;

class Temp_Schema extends Base_Schema {

	public $type = 'json';

	public $site = 6;

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

		$this->post_type_keys = Map::factory()->get_post_type_keys();

		return Map::factory()->schema();
	}

}