<?php

namespace TenUp\Exodus;

class Exodus extends \WP_CLI_Command{

	public function hello( $args = array(), $assoc_args = array() ) {
		list( $name ) = $args;
		\WP_CLI::success( "Hello $name." );
	}

}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'exodus', '\TenUp\Exodus\Exodus' );
}