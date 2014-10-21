<?php

namespace TenUp\Exodus\Report;


class Report {

	protected $rows = array();

	protected $header = array( 'Original URL', 'New URL' );

	function __construct(  ) {
		$this->rows[] = $this->header;
	}

	public function add_row( $data ) {
		$this->rows[] = $data;
	}

	public function generate(){
		$report = fopen( WP_CONTENT_DIR . '/migrations/URL-Report-' . date( 'Y-m-d', strtotime('now') ) . '.csv', 'w');

		foreach ($this->rows as $fields ) {
			fputcsv($report, $fields);
		}

		fclose($report);

		\WP_CLI::success( 'Your migration report was generated' );
	}
}