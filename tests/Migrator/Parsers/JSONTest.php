<?php

use TenUp\Exodus\Migrator\Parsers\JSON as JSON;

function is_email() {
	return false;
}

class JSONTest extends PHPUnit_Framework_TestCase {

	protected $json_parser;

	protected function setUp() {
		$schema = Mockery::mock( 'TenUp\Exodus\Schema\Base_Schema' );
		$schema->shouldReceive( 'build' )->andReturn( $this->get_schema_build() )->once();
		$schema->shouldReceive( 'keys' )->andReturn( $this->get_key_build() )->once();

		$this->json_parser = new JSON( $this->get_data(), $schema );
	}

	public function testContentIsGettingPopulated() {
		$this->assertEquals( 2, $this->json_parser->get_content_count() );
		$this->assertCount( 2, $this->json_parser->get_content() );
	}

	public function testUpdateIterator() {
		$data = json_decode( json_encode( array(
			"item" => array(
				"child" => array(
					"childitem" => 'content'
				)
			)
		) ), false );

		$iterator = $this->invokeMethod( $this->json_parser, 'update_iterator_path', array( $data ) );
		$this->assertObjectHasAttribute( 'item', $iterator );

		$this->json_parser->schema->iterator = 'child.childitem';
		$update_iterator                     = $this->invokeMethod( $this->json_parser, 'update_iterator_path', array( $data->item ) );
		$this->assertEquals( 'content', $update_iterator );
	}

	public function testBuildingPostObject() {
		$content = $this->json_parser->data;
		$import_object = $this->invokeMethod( $this->json_parser, 'build_post_object', array( $content[0] ) );

		$this->assertObjectHasAttribute( 'post_title', $import_object );
		$this->assertObjectHasAttribute( 'post_date', $import_object );
		$this->assertObjectHasAttribute( 'post_content', $import_object );
		$this->assertObjectHasAttribute( 'post_author', $import_object );
		$this->assertObjectHasAttribute( 'user_login', $import_object->post_author );
	}

	protected function tearDown() {
		\Mockery::close();
	}

	public function invokeMethod( &$object, $methodName, array $parameters = array() ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method     = $reflection->getMethod( $methodName );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $parameters );
	}

	protected function get_data() {
		return '[{
	        "timestamp": 1408464060,
	        "title": "Test title",
	        "body": "body content",
	        "author": "John Smith"
	      },{
	        "timestamp": 1408677660,
	        "title": "Test title 2",
	        "body": "body content 2",
	        "author": "John Smith"
	      }]';
	}

	protected function get_schema_build() {
		return array(
			'post' => array(
				'post_title'   => 'title',
				'post_date'    => 'timestamp',
				'post_content' => 'body',
				'post_author'  => 'author',
			)
		);
	}

	protected function get_key_build() {
		return array( 'post' => 'post' );
	}
}
 