<?php

use org\bovigo\vfs\vfsStream as vfsStream;
use TenUp\Exodus\Report\Report as Report;

class ReportTest extends PHPUnit_Framework_TestCase {

	protected $root;

	public static function setUpBeforeClass() {
		require_once dirname( __DIR__ ) . '/test-tools/wp-cli-mock.php';
	}

	protected function setUp(){
		$this->root = vfsStream::setup( 'root' );
		mkdir( vfsStream::url('root') . '/migrations/' , 0755, false );
	}

	public function testCreateReport() {
		$report = new Report( 'test-report', array( 'Old URL', 'New URL' ) );
		$report->add_row( array( 'data_1', 'data_1') );
		$report->add_row( array( 'data_1', 'data_1') );
		$report->add_row( array( 'data_1', 'data_1') );

		date_default_timezone_set('America/New_York');
		$file_name = 'test-report-' . date( 'Y-m-d', strtotime('now') ) . '.csv';
		$this->assertTrue( $this->root->hasChild( 'migrations' ) );
		$this->assertFalse( $this->root->hasChild( 'migrations/' . $file_name ) );
		$report->generate(  vfsStream::url('root') . '/migrations/' );
		$this->assertTrue( $this->root->hasChild( 'migrations/' . $file_name  ) );

		$csv = file( vfsStream::url('root') . '/migrations/' . $file_name , FILE_SKIP_EMPTY_LINES);
		$this->assertEquals( 4, count( $csv ) );
	}
}
