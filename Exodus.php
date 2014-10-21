<?php

namespace TenUp\Exodus;

use TenUp\Exodus\Migrator\Parsers\JSON;
use TenUp\Exodus\Migrator\Migrator;
use TenUp\Exodus\Schema\Console\Schema_Command;

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	require_once 'vendor/autoload.php';

	class Exodus extends \WP_CLI_Command {

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
		public function migrate( $args = array(), $assoc_args = array() ) {
			$file  = $args[0];
			$force = isset( $assoc_args['force'] ) ? true : false;

			if ( false !== strpos( $file, '.xml' ) || false !== strpos( $file, '.sql' ) || false !== strpos( $file, '.json' ) ) {
				\WP_CLI::line( 'Loading ' . $file . '...' );
			} else {
				\WP_CLI::error( 'Error: a valid file type must be provided' );
			}

			$data = file_get_contents( $file );

			if ( ! $data ) {
				\WP_CLI::error( 'Error: could not load the specified file' );
			}

			if ( ! $migration_files = $this->get_migration_files() ) {
				\WP_CLI::error( 'Error: a schema file must be generate' );
			}

			foreach ( $migration_files as $file ) {
				include_once WP_CONTENT_DIR . '/migrations/' . $file;
				$class  = $this->class_name( $file );
				$schema = new $class;
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
		}

		/**
		 * Create schema file.
		 *
		 * @synopsis <name> --type=<schema_type> [--post_types=<types>] [--site=<site_id>]  [--iterator=<iterator>]  [--report=<report_type>]
		 */
		public function schema( $args = array(), $assoc_args = array() ) {
			$schema_args         = array();
			$schema_args['name'] = $args[0];
			$schema_args['type'] = $assoc_args['type'];
			if ( isset( $assoc_args['post_types'] ) ) {
				$schema_args['post_types'] = $assoc_args['post_types'];
			}
			if ( isset( $assoc_args['site'] ) ) {
				$schema_args['site'] = $assoc_args['site'];
			}
			if ( isset( $assoc_args['iterator'] ) ) {
				$schema_args['iterator'] = $assoc_args['iterator'];
			}
			if ( isset( $assoc_args['report'] ) ) {
				$schema_args['report'] = $assoc_args['report'];
			}
			$schema = new Schema_Command();
			$schema->create_migration_file( $schema_args );

			\WP_CLI::success( $schema_args['name'] . ' migration file was generated' );
		}

		protected function get_migration_files() {
			$files = array();
			if ( ! file_exists( WP_CONTENT_DIR . '/migrations/' ) ) {
				return false;
			}

			if ( $handle = opendir( WP_CONTENT_DIR . '/migrations/' ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file !== "." && $file !== ".." && false !== strpos( strtolower( $file ), '.php' ) ) {
						$files[] = $file;
					}
				}
				closedir( $handle );

				return $files;
			}

			return false;
		}

		protected function class_name( $file ) {
			return Schema_Command::sanitize_class_name( str_replace( '-', '_', str_replace( '.php', '', $file ) ) );
		}

	}

	\WP_CLI::add_command( 'exodus', '\TenUp\Exodus\Exodus' );

}