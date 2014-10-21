<?php

use TenUp\Exodus\Schema\Map as Map;

class MapTest extends PHPUnit_Framework_TestCase {

	protected $output = array(
		'post_title'   => 'title',
		'post_type'    => 'page_type',
		'post_content' => 'body',
		'meta_data'    => array(
			'post_url' => 'post_url',
			'post_id'  => 'id',
		),
		'taxonomy'     => array(
			'post_tag' => 'tags'
		)
	);

	public function setUp() {
		Map::factory()->create( 'post', function ( $data ) {
			$data->post_title( 'title' );
			$data->post_type( 'post_type' );
			$data->post_content( 'body' );
			$data->post_date( 'timestamp' );
			$data->meta_data( 'post_url', 'post_url' );
			$data->meta_data( 'post_id', 'id' );
			$data->taxonomy( 'post_tag', 'tags' );
		} );

		Map::factory()->create( 'page', function ( $data ) {
			$data->post_title( 'title' );
			$data->post_type( 'page_type' );
			$data->post_content( 'body' );
			$data->meta_data( 'post_url', 'post_url' );
			$data->meta_data( 'post_id', 'id' );
			$data->taxonomy( 'post_tag', 'tags' );
		} );
	}

	public function testMapsWhereCreated() {
		$schema = Map::factory()->schema();
		$this->assertCount( 2, $schema );
	}

	public function testSchemaOutput() {
		$schema = Map::factory()->schema();
		$this->assertEquals( $this->output, $schema['page'] );
	}

	public function testPostKeysAreGettingSet() {
		$post_keys = Map::factory()->keys();
		$this->assertArrayHasKey( 'post_type', $post_keys );
		$this->assertContains( 'post', $post_keys );
		$this->assertArrayHasKey( 'page_type', $post_keys );
		$this->assertContains( 'page', $post_keys );
	}

}
 