<?php

namespace TenUp\Exodus\Migrator;

use TenUp\Exodus\Migrator\Parsers\Base_Parser;
use TenUp\Exodus\Report\Report;

class Migrator extends Base_Importer{

	protected $parser;

	protected $schema;

	protected $report;

	function __construct( Base_Parser $parser, $force ) {
		$this->parser = $parser;
		$this->force = $force;
	}

	public function add_report( Report $report ){
		$this->report = $report;
	}

	public function run(){

		if( isset( $this->parser->schema->site ) ){
			switch_to_blog( (int) $this->parser->schema->site );
		}

		$total = $this->parser->total;

		if( $total > 0){
			$notify = new \cli\progress\Bar( "There's $total total post being imported.", $total );

			foreach( $this->parser->get_content() as $key => $content ){
				if( $id = $this->insert_post( $content, $this->force ) ){
					if( isset( $this->report ) ){
						$url = $this->parser->schema->report;
						$this->report->add_row( array( $this->parser->data[ $key ]->$url , get_the_permalink( $id ) ) );
					}
					$notify->tick();
				}
			}
			$notify->finish();
			\WP_CLI::success( 'Your migration is complete' );

			if( isset( $this->report ) ){
				$this->report->generate();
			}
		}
	}
}