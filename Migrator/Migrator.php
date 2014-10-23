<?php

namespace TenUp\Exodus\Migrator;

use TenUp\Exodus\Migrator\Parsers\Base_Parser;
use TenUp\Exodus\Report\Report;

/**
 * Class Migrator
 * @package TenUp\Exodus\Migrator
 */
class Migrator extends Base_Importer {

	/**
	 * @var Base_Parser instance of the injected parser
	 */
	protected $parser;

	/**
	 * @var Report instance of the injected report
	 */
	protected $report;

	/**
	 * Setup Migrator dependencies.
	 *
	 * @param Base_Parser $parser
	 * @param             $force
	 */
	function __construct( Base_Parser $parser, $force ) {
		$this->parser = $parser;
		$this->force  = $force;
	}

	/**
	 * Inject Report to Migrator.
	 *
	 * @param Report $report
	 */
	public function add_report( Report $report ) {
		$this->report = $report;
	}

	/**
	 * Run the migration by grabbing the content from the parser and
	 * inserts it into the database. It also checks to see if a Report
	 * has been injected in order to generate it.
	 */
	public function run( $pretend = false ) {

		if ( isset( $this->parser->schema->site ) ) {
			switch_to_blog( (int) $this->parser->schema->site );
		}

		$total = $this->parser->get_content_count();
		$count = 0;

		if ( $total > 0 ) {
			$notify = new \cli\progress\Bar( "There's $total total post being imported.", $total );

			foreach ( $this->parser->get_content() as $key => $content ) {
				if ( $id = $this->insert_post( $content, $this->force, $pretend ) ) {
					$count ++;
					if ( isset( $this->report ) ) {
						$url = $this->parser->schema->report;
						$this->report->add_row( array( $this->parser->data[ $key ]->$url, get_the_permalink( $id ) ) );
					}
					$notify->tick();
				}
			}
			$notify->finish();
			\WP_CLI::success( 'Your migration is complete. ' . $count . ' of ' . $total . ' post were migrated successfully!' );

			if ( isset( $this->report ) ) {
				$this->report->generate( EXODUS_DIR );
			}

			return $count;
		}

		return false;
	}
}