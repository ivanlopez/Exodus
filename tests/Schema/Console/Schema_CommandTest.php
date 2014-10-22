<?php

use org\bovigo\vfs\vfsStream as vfsStream;
use TenUp\Exodus\Schema\Console\Schema_Command as Schema_Command;

class Schema_CommandTest extends PHPUnit_Framework_TestCase {

	protected $schema;

	protected $root;

	protected function setUp() {
		$this->root   = vfsStream::setup( 'root' );
		$this->schema = new Schema_Command( vfsStream::url( 'root' ) . '/migrations/' );
	}

	public function testCreateMigrationDirectory() {
		$this->assertTrue( $this->root->hasChild( 'migrations' ) );
	}

	public function testSchemaFileCreated() {
		$this->assertFalse( $this->root->hasChild( 'migrations/test-migration.php' ) );
		$this->schema->create_migration_file( array(
			'name' => 'test_migration',
			'type' => 'json'
		) );
		$this->assertTrue( $this->root->hasChild( 'migrations/test-migration.php' ) );

		$expected_output = file_get_contents( __DIR__ . '/stubs/schema.stub' );
		$actual_output   = file_get_contents( vfsStream::url( 'root/migrations/test-migration.php' ) );

		$this->assertEquals( $expected_output, $actual_output );
	}
}
 