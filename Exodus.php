<?php

namespace TenUp\Exodus;

class Exodus {

	/**
	 * Place holder.
	 */
	function __construct() {
	}

	/**
	 * Return a singleton instance of the class.
	 *
	 * @return Exodus
	 */
	public static function factory() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}

	public function setup() {

	}

}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	Exodus::factory();
}