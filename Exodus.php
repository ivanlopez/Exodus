<?php

namespace TenUp\Exodus;

class Exodus extends \WP_CLI_Command{

	/**
	 * Hello test command
	 *
	 * @synopsis <name>
	 */
	public function hello( $args = array(), $assoc_args = array() ) {
		list( $name ) = $args;
		\WP_CLI::success( "Hello $name." );
	}

	/**
	 * Handle content import.
	 *
	 * @synopsis <file> --type=<import-type> --schema=<mapping-schema> --site=<site> [--force]
	 */
	public function migrate( $args = array(), $assoc_args = array() ){

	}

}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'exodus', '\TenUp\Exodus\Exodus' );
}