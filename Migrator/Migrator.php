<?php

namespace TenUp\Exodus\Migrator;

use TenUp\Exodus\Migrator\Parsers\Base_Parser;
use TenUp\Exodus\Report\Report;
use TenUp\Exodus\Database\Validator;

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
	 * @var array instance of the injected report
	 */
	protected $report;

	/**
	 * @var Validator instance of the injected validator
	 */
	protected $validator;

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
	public function add_report( Report $report, $name ) {
		$this->report[ $name ] = $report;
	}

	/**
	 * Inject Report to Migrator.
	 *
	 * @param Report $report
	 */
	public function add_validator( Validator $validator ) {
		$this->validator = $validator;
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
					if ( isset( $this->parser->schema->report ) ) {
						$url = $this->parser->schema->report;
						$this->report['url']->add_row( array(
								$this->parser->data[ $key ]->$url,
								get_the_permalink( $id )
							) );
					}
					if ( isset( $this->validator ) ) {
						if ( $this->validator->should_compare( $key ) && ! ( $post = $this->validator->compare( $content, $id ) ) ) {
							//Content did not match so we add the post to the report
							$this->report['validator']->add_row( array(
								$key,
								$post->post_title,
								$post->guid
							) );
						}
					}
					$notify->tick();
				}
			}
			$notify->finish();
			\WP_CLI::success( 'Your migration is complete. ' . $count . ' of ' . $total . ' post were migrated successfully!' );

			if ( isset( $this->report ) ) {
				foreach ( $this->report as $report ) {
					if ( count( $report->rows ) > 1 ) {
						$report->generate( EXODUS_DIR );
					}
				}
			}

			if ( isset( $this->validator ) ) {
				$total_checked = count( $this->validator->check );
				$pass = $total_checked - ( count( $this->report['validator']->rows ) - 1);
				\WP_CLI::success( 'Your validation is complete. ' . $this->parser->schema->verify . '% of the imported post were checked and '. $pass . ' of ' . $total_checked . ' validated successfully!' );
			}

			return $count;
		}

		return false;
	}
}