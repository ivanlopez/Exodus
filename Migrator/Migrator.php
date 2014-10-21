<?php

namespace TenUp\Exodus\Migrator;

use TenUp\Exodus\Migrator\Parsers\Base_Parser;

class Migrator extends Base_Importer{

	protected $parser;

	protected $schema;

	function __construct( Base_Parser $parser, $force) {
		$this->parser = $parser;
		$this->force = $force;
	}

	public function run(){

		if( isset( $this->parser->schema->site ) ){
			switch_to_blog( (int) $this->parser->schema->site );
		}

		$total = $this->parser->total;

		if( $total > 0){
			$notify = new \cli\progress\Bar( "There's $total total post being imported.", $total );

			foreach( $this->parser->get_content() as $content ){
				if( $this->insert_post( $content, $this->force ) ){
					$notify->tick();
				}
			}
			$notify->finish();
			\WP_CLI::success( 'Your migration is complete' );
		}
	}
}