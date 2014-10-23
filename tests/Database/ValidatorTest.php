<?php

use TenUp\Exodus\Database\Validator as Validator;
use TenUp\Exodus\TestCase as TestCase;

class ValidatorTest extends TestCase {

	public function setUp() {
		parent::setUp();
		$this->validator = new Validator();
	}

	public function testValidatorSetup() {
		$validator = new Validator();
		$this->assertCount( 0, $validator->check );

		$validator->setUp( 50, 10000 );
		$this->assertCount( 5000, $validator->check );

		$validator->setUp( 33, 92512 );
		$this->assertCount( 30529, $validator->check );

		$validator->setUp( 73.33333, 9999 );
		$this->assertCount( 7333, $validator->check );

		$validator->setUp( 10, 100 );
		$this->assertCount( 10, $validator->check );

		$validator->setUp( 1, 1 );
		$this->assertCount( 1, $validator->check );
	}

	public function testShouldCompare() {
		$validator = new Validator();
		$validator->setUp( 10, 100 );
		$tested_items = array();

		foreach ( range( 0, 99 ) as $item ) {
			if ( $validator->should_compare( $item ) ) {
				$tested_items[] = $item;
			}
		}

		$this->assertEquals( $tested_items, $validator->check );
		$this->assertCount( 10, $tested_items );
	}

	public function testCompare() {
		\WP_Mock::wpFunction( 'get_post', array(
			'times'  => '4',
			'args'   => array( 1 ),
			'return' => $this->get_post_object(),
		) );

		\WP_Mock::wpFunction( 'get_post_meta', array(
			'times'  => '2',
			'args'   => array( 1 ),
			'return' => array(
				'old_id'  => array( 9 ),
				'old_url' => array( 'http://test.com' )
			),
		) );

		$category_terms = array( 'Category 1', 'Category 2' );
		$custom_tax     = array( 'Term 1', 'Term 2' );

		\WP_Mock::wpFunction( 'has_term', array(
			'times'  => '4',
			'args'   => array(
				\Mockery::anyOf( $category_terms, $custom_tax ),
				\Mockery::anyOf( 'category', 'custom_tax' ),
				1
			),
			'return' => true,
		) );

		$validator = new Validator();
		$this->assertTrue( $validator->compare( $this->get_imported_content(), 1 ) );
		$this->assertTrue( $validator->compare( $this->get_imported_content( true ), 1 ) );
		$this->assertTrue( $validator->compare( $this->get_imported_content( false, true ), 1 ) );
		$this->assertTrue( $validator->compare( $this->get_imported_content( true, true ), 1 ) );
	}

	protected function get_imported_content( $with_tax = false, $with_meta = false ) {
		$post               = new stdClass();
		$post->post_title   = 'Test title';
		$post->post_type    = 'post';
		$post->post_content = 'body content';
		$post->post_date    = '2014-08-19 12:01:00';
		$post->post_author  = 1;

		if ( $with_tax ) {
			$post->taxonomy = array(
				'category'   => array(
					'Category 1',
					'Category 2'
				),
				'custom_tax' => array(
					'Term 1',
					'Term 2'
				),
			);
		}

		if ( $with_meta ) {
			$post->meta_data = array(
				'old_id'  => 9,
				'old_url' => 'http://test.com'
			);
		}

		return $post;
	}

	protected function  get_post_object() {
		$post                        = new stdClass();
		$post->post_title            = 'Test Title';
		$post->post_type             = 'post';
		$post->post_content          = 'body content';
		$post->post_date             = '2014-08-19 12:01:00';
		$post->post_author           = 1;
		$post->post_date             = '2014-08-19 12:01:00';
		$post->post_date_gmt         = '2014-08-19 12:01:00';
		$post->post_content_filtered = '';
		$post->post_status           = 'publish';
		$post->comment_status        = '';
		$post->ping_status           = '';
		$post->post_password         = '';
		$post->post_name             = 'test-title';
		$post->to_ping               = '';
		$post->pinged                = '';
		$post->post_modified         = '2014-08-19 12:01:00';
		$post->post_modified_gmt     = '2014-08-19 12:01:00';
		$post->post_parent           = '';
		$post->menu_order            = '';
		$post->guid                  = 'http://newurl.com/test-title';
	}
}
 