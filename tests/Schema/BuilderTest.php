<?php

use \TenUp\Exodus\Schema\Builder as Builder;

class BuilderTest extends PHPUnit_Framework_TestCase {

	protected $builder;

	public function setUp() {
		$this->builder = new Builder( function( $data ){
			$data->post_title('title');
		} );
	}

	public function testSettingPostType() {
		$this->assertEquals( 'post', $this->builder->get_post_type() );
		$this->builder->post_type( 'test_post' );
		$this->assertEquals( 'test_post', $this->builder->get_post_type() );

		$this->builder->post_type( 'test_post2' );
		$this->assertEquals( 'test_post2', $this->builder->get_post_type() );
	}

	public function testSettingPostMeta() {
		$this->builder->meta_data( 'meta1', 'meta1' );
		$this->builder->meta_data( 'meta2', 'meta2' );
		$data = $this->builder->get_data();
		$this->assertCount( 2, $data['meta_data'] );
		$this->assertArrayHasKey( 'meta1', $data['meta_data'] );
	}

	public function testSettingPostTaxonomy() {
		$this->builder->taxonomy( 'tax1', 'tax1' );
		$this->builder->taxonomy( 'tax2', 'tax2' );
		$this->builder->taxonomy( 'tax3', 'tax3' );
		$data = $this->builder->get_data();
		$this->assertCount( 3, $data['taxonomy'] );
		$this->assertArrayHasKey( 'tax2', $data['taxonomy'] );
	}

}
 