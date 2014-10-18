<?php

namespace TenUp\Exodus;

use TenUp\Exodus\Migrator\JSON;

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	require_once 'vendor/autoload.php';

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
	 * @synopsis <file> [--force]
	 */
	public function migrate( $args = array(), $assoc_args = array() ){
		$file = $args[0];
		if(  false !== strpos( $file, '.xml' ) || false !==  strpos( $file, '.sql' ) || false !==  strpos( $file, '.json' ) ){
			\WP_CLI::line( 'Loading ' . $file . '...' );
		} else {
			\WP_CLI::error( 'Error: a valid file type must be provided' );
		}

		/*if( !isset( $assoc_args['schema'] ) ){
			\WP_CLI::error( 'Error: a schema file must be generate' );
		} else {*/

			include_once 'Temp_Schema.php';
			$schema = new Temp_Schema();
		//}

		$force = isset( $assoc_args['force'] ) ? true : false;

		switch ( $schema->type ) {
			case 'json':
				$data = file_get_contents( $file );
				if( ! $data ){
					\WP_CLI::error( 'Error: could not load the specified file' );
				}
				$data = json_decode( $data );
				$importer = new JSON( $data, $schema , $force );
				break;
			case 'sql':
				break;
			case 'xml':
				break;
		}

		$importer->import();
	}

}

\WP_CLI::add_command( 'exodus', '\TenUp\Exodus\Exodus' );

}