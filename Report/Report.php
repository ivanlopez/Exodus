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
	protected $rows = array();

	/**
	 * @var array list of header for the report
	 */
	protected $header = array( 'Original URL', 'New URL' );

	/**
	 * Setup header in the report.
	 */
	function __construct(  ) {
		$this->rows[] = $this->header;
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
	public function generate( $directory ){
		$report = fopen( $directory . 'URL-Report-' . date( 'Y-m-d', strtotime('now') ) . '.csv', 'w');

		foreach ($this->rows as $fields ) {
			fputcsv($report, $fields);
		}

		fclose($report);

		\WP_CLI::success( 'Your migration report was generated!' );
	}
}