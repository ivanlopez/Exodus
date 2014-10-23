<?php

namespace TenUp\Exodus\Report;

/**
 * Class Report
 * @package TenUp\Exodus\Report
 */
class Report {

	/**
	 * @var array list of rows to be exported in the report
	 */
	public $rows = array();

	/**
	 * @var string file name for your report
	 */
	protected $name = 'URL-Report';

	/**
	 * Setup header in the report.
	 */
	function __construct( $name, $header = null ) {
		$this->name = $name;
		if ( ! is_null( $header ) ) {
			$this->rows[] = $header;
		}
	}

	/**
	 * Add a new row to the report.
	 *
	 * @param array $data
	 */
	public function add_row( $data ) {
		$this->rows[] = $data;
	}

	/**
	 * Generate a new CSV based on the rows added to this report.
	 */
	public function generate( $directory ) {
		$report = fopen( $directory . $this->name . '-' . date( 'Y-m-d', strtotime( 'now' ) ) . '.csv', 'w' );

		foreach ( $this->rows as $fields ) {
			fputcsv( $report, $fields );
		}

		fclose( $report );

		\WP_CLI::success( 'Your migration report was generated!' );
	}
}