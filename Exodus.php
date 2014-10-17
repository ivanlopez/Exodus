<?php

namespace TenUp\Exodus;

use TenUp\Exodus\Migrator\Migrator;
use TenUp\Exodus\Migrator\Module\JSON;

if ( defined( 'WP_CLI' ) && WP_CLI ) {

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
	 * @synopsis <file> --schema=<mapping-schema> [--force]
	 */
	public function migrate( $args = array(), $assoc_args = array() ){
		$file = $args[0];
		if(  false !== strpos( $file, '.xml' ) || false !== strpos( $file, '.sql' ) || false !== strpos( $file, '.json' ) ){
			WP_CLI::error( 'Error: a valid file type must be provided!' );
		}

		if( !isset( $assoc_args['schema'] ) ){
			WP_CLI::error( 'Error: a schema file must be generate' );
		} else {
			$schema = new Temp_Schema();
		}

		switch ( $schema->type() ) {
			case 'json':
				$importer = new JSON( $file,  new Temp_Schema() );
				break;
			case 'sql':
				break;
			case 'xml':
				break;
		}

		$migration = new Migrator( $importer );
	}

}

\WP_CLI::add_command( 'exodus', '\TenUp\Exodus\Exodus' );

}