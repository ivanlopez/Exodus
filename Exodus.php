<?php

namespace TenUp\Exodus;

use TenUp\Exodus\Database\Validator;
use TenUp\Exodus\Migrator\Parsers\JSON;
use TenUp\Exodus\Migrator\Migrator;
use TenUp\Exodus\Report\Report;
use TenUp\Exodus\Schema\Console\Schema_Command;

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	require_once 'vendor/autoload.php';

	define( 'EXODUS_DIR', WP_CONTENT_DIR . '/migrations/' );

	/**
	 * Exodus is a content migration library that allows you to
	 * dynamically create content migrations.
	 * @package TenUp\Exodus
	 */
	class Exodus extends \WP_CLI_Command {

		/**
		 * Hello test command
		 *
		 * <name> string
		 *
		 * @synopsis <name>
		 */
		public function hello( $args = array(), $assoc_args = array() ) {
			list( $name ) = $args;
			\WP_CLI::success( "Hello $name." );
		}

		/**
		 * Content migration command
		 *
		 * In order to use this command a file must me passed in and
		 * a migration file must be generated.
		 *
		 * <migration>  name of the migration ( file name without .php )
		 * --file       path to a valid file. Supported file formats are xml, sql, and json
		 * [--force]    skip to see if post already exist before importing
		 *
		 * @synopsis <migration> --file=<file_path> [--force]
		 */
		public function migrate( $args = array(), $assoc_args = array() ) {
			$migration  = $args[0];
			$file  = $assoc_args['file'];
			$force = isset( $assoc_args['force'] ) ? true : false;

			if ( false !== strpos( $file, '.xml' ) || false !== strpos( $file, '.sql' ) || false !== strpos( $file, '.json' ) ) {
				\WP_CLI::line( 'Loading ' . $file . ' ...' );
			} else {
				\WP_CLI::error( 'Error: A valid file type must be provided.' );
			}

			$data = file_get_contents( $file );

			if ( ! $data ) {
				\WP_CLI::error( 'Error: Could not load the specified file.' );
			}

			if ( ! $migration_file = $this->get_migration_file( $migration ) ) {
				\WP_CLI::error( 'Error: The ' . $migration . ' schema file was not found.' );
			}

			include_once EXODUS_DIR . $migration_file;
			$class  = $this->class_name( $migration_file );
			$schema = new $class;
			switch ( $schema->type ) {
				case 'json':
					$parser = new JSON( $data, $schema );
					\WP_CLI::line( 'Parsing JSON data ...' );
					break;
				case 'sql':
					#TODO: SQL parser still needs to be created
					\WP_CLI::error( 'Error: SQL parser still needs to be created.' );
					break;
				case 'xml':
					#TODO: XML parser still needs to be created
					\WP_CLI::error( 'Error: XML parser still needs to be created.' );
					break;
			}

			$migrator    = new Migrator( $parser, $force );
			$report_name = str_replace( '_', '-', $class );

			if ( isset( $schema->report ) ) {
				$migrator->add_report( new Report( $report_name . '-URL-Report', array(
							'Old URL',
							'New URL'
						) ), 'url' );
			}

			if ( isset( $schema->verify ) ) {
				$validator = new Validator();
				$validator->setup( $schema->verify, $parser->get_content_count() );
				$migrator->add_validator( $validator );
				$migrator->add_report( new Report( $report_name . '-Validator-Report', array(
							'Original Content Key',
							'Post Title',
							'Post URL'
						) ), 'validator' );
			}

			$migrator->run();
		}

		/**
		 * Create migration schema file.
		 *
		 *  <name>          the name of your migration
		 * --type           the type of migration. You can use json, sql or xml
		 * [--post_types]   comma delimited list of the post types that will be imported. By default it will only do post
		 * [--site]         the id of the site you are migrating content to
		 * [--iterator]     the nesting of where the post are in your import file
		 * [--report]       name of the url parameter in your import file in order to export a csv of old to new urls
		 * [--verify]       percent (1-100) of random post to verify that were imported successfully
		 *
		 * @synopsis <name> --type=<schema_type> [--post_types=<types>] [--site=<site_id>]  [--iterator=<iterator>]  [--report=<url_key>]  [--verify=<percent>]
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
			if ( isset( $assoc_args['verify'] ) ) {
				$verify                = (int) $assoc_args['verify'];
				$verify                = ( $verify > 100 ) ? 100 : $verify;
				$schema_args['verify'] = $verify;
			}
			$schema = new Schema_Command( EXODUS_DIR );
			$schema->create_migration_file( $schema_args );

			\WP_CLI::success( $schema_args['name'] . ' migration file was generated. You can find the migration file in the wp-content/migrations/ directory.' );
		}

		/**
		 * Look in the migrations directory and retrieve all the files
		 * with a .php extension.
		 *
		 * @return array|bool
		 */
		protected function get_migration_file( $file_slug ) {

			if ( ! file_exists( EXODUS_DIR ) ) {
				return false;
			}

			if ( $handle = opendir( EXODUS_DIR ) ) {
				while ( false !== ( $file = readdir( $handle ) ) ) {
					if ( $file !== "." && $file !== ".." && false !== strpos( strtolower( $file ), $file_slug .'.php' ) ) {
						$files = $file;
						break;
					}
				}
				closedir( $handle );

				return $files;
			}

			return false;
		}

		/**
		 * Retrieve Migration class name from migration file.
		 *
		 * @param $file
		 *
		 * @return string
		 */
		protected function class_name( $file ) {
			return Schema_Command::sanitize_class_name( str_replace( '-', '_', str_replace( '.php', '', $file ) ) );
		}

	}

	/**
	 * Exodus command
	 */
	\WP_CLI::add_command( 'exodus', '\TenUp\Exodus\Exodus' );

}