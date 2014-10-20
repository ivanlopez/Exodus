<?php

namespace TenUp\Exodus;

use TenUp\Exodus\Migrator\Parsers\JSON;
use TenUp\Exodus\Migrator\Migrator;
use TenUp\Exodus\Schema\Console\Schema_Command;

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

		$data = file_get_contents( $file );

		if( ! $data ){
			\WP_CLI::error( 'Error: could not load the specified file' );
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
				$parser = new JSON( $data, $schema );
				break;
			case 'sql':
				break;
			case 'xml':
				break;
		}

		$migrator = new Migrator( $parser, $force );
		$migrator->run();
	}

	/**
	 * Create schema file.
	 *
	 * @synopsis <name> --type=<schema_type> [--post_types=<types>] [--site=<site_id>]  [--iterator=<iterator>]  [--report=<report_type>]
	 */
	public function schema( $args = array(), $assoc_args = array() ){
		$schema_args = array();
		$schema_args['name'] = $args[0];
		$schema_args['type'] = $assoc_args['type'];
		if( isset( $assoc_args['post_types'] ) ){
			$schema_args['post_types'] = $assoc_args['post_types'];
		}
		if( isset( $assoc_args['site_id'] ) ){
			$schema_args['site'] = $assoc_args['site_id'];
		}
		if( isset( $assoc_args['iterator'] ) ){
			$schema_args['iterator'] = $assoc_args['iterator'];
		}
		if( isset( $assoc_args['report'] ) ){
			$schema_args['report'] = $assoc_args['report'];
		}
		$schema = new Schema_Command();
		$schema->create_migration_file( $schema_args );
	}

}

\WP_CLI::add_command( 'exodus', '\TenUp\Exodus\Exodus' );

}

//CREATE SCHEMA FILE
//CREATE REDIRECT REPORT