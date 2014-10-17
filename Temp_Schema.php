<?php

namespace TenUp\Exodus;


use TenUp\Exodus\Schema\Base_Schema;
use TenUp\Exodus\Schema\Map;

class Temp_Schema extends Base_Schema{

	public $type = 'json';

	public $site = 'test.com';

	public function build(){

		Map::factory()->create( 'post', function( $data ){
			$data->post_title('title');
			$data->post_content('content');
			$data->post_author( array(
				'email' => 'adsfasdf',
				'user' => ''
			));
			$data->post_excerpt('excerpt');
			$data->post_date('date');
			$data->meta_data('key', 'key_value');
			$data->meta_data('key1', 'key_value1');
			$data->meta_data('key2', 'key_value2');
			$data->taxonomy('taxonomy', 'tax1');
			$data->taxonomy('taxonomy2', 'tax2');
		} );

		return Map::factory()->schema();
	}

}