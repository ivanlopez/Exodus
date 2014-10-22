<?php

use TenUp\Exodus\Migrator\Migrator as Migrator;

class MigratorTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		require_once dirname( __DIR__ ) . '/test-tools/wp-cli-mock.php';
		require_once dirname( __DIR__ ) . '/test-tools/bar-mock.php';
	}

	public function testRunMigrator() {

		$parser = \Mockery::mock( 'TenUp\Exodus\Migrator\Parsers\Base_Parser');
		$parser->shouldReceive('get_content')->andReturn( array( 'foo', 'bar' ) )->once();
		$parser->shouldReceive('get_content_count')->andReturn( 2 )->once();

		$migrator = new Migrator( $parser, false );
		$this->assertEquals( 2, $migrator->run( true ) );
	}

	protected function tearDown() {
		\Mockery::close();
	}

}
