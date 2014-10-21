<?php

namespace TenUp\Exodus\Schema\Console;

class Schema_Command {

	function __construct() {
		$this->create_migration_directory();
	}

	protected function create_migration_directory() {
		if ( ! file_exists( WP_CONTENT_DIR . '/migrations/' ) ) {
			mkdir( WP_CONTENT_DIR . '/migrations/', 0755, false );
		}
	}

	public function create_migration_file( $args ) {
		$stub       = file_get_contents( __DIR__ . '/stubs/schema.stub' );
		$stub       = str_replace( '{name}', $this->sanitize_class_name( $args['name'] ), $stub );
		$map        = "";
		$propertied = "public \$type = '" . $args['type'] . "';\n\n";

		if ( isset( $args['site'] ) ) {
			$propertied .= "	public \$site = " . $args['site'] . ";\n\n";
		}

		if ( isset( $args['iterator'] ) ) {
			$propertied .= "	public \$iterator = '" . $args['iterator'] . "';\n\n";
		}

		if ( isset( $args['report'] ) ) {
			$propertied .= "	public \$report = '" . $args['report'] . "';\n\n";
		}

		$stub = str_replace( '{properties}', self::remove_extra_return( $propertied ), $stub );

		if ( isset( $args['post_types'] ) ) {
			$post_types = explode( ',', $args['post_types'] );
			$first_loop = true;
			if ( count( $post_types ) > 0 ) {
				foreach ( $post_types as $post ) {
					if( !$first_loop  ){
						$map .= "		";
					}
					$map .= "Map::factory()->create( '" . strtolower( $post ) . "', function ( \$data ) {
						\$data->post_title( 'title' );
						\$data->post_content( 'content' );
						\$data->post_date( 'timestamp' );
					} );\n\n";
					$first_loop = false;
				}
			}
		} else {
			$map = "Map::factory()->create( 'post', function ( \$data ) {
				\$data->post_title( 'title' );
				\$data->post_content( 'content' );
				\$data->post_date( 'timestamp' );
			} );\n\n";

		}
		$stub = str_replace( '{maps}', $this->remove_extra_return( $map ), $stub );

		$migration = fopen( WP_CONTENT_DIR . '/migrations/' . self::sanitize_file_name( $args['name'] ) . '.php', "w" );
		fwrite( $migration, $stub );
		fclose( $migration );
	}

	public static function sanitize_class_name( $name ) {
		$class_parts = array();
		$class       = preg_replace( '#[^a-zA-Z_]#', '', $name );
		$class       = explode( '_', $name );
		foreach ( $class as $part ) {
			$class_parts[] = ucwords( $part );
		}

		return implode( '_', $class_parts );
	}

	public static function sanitize_file_name( $name ) {
		$class_parts = array();
		$class       = preg_replace( '#[^a-zA-Z_]#', '', $name );
		$class       = explode( '_', $name );
		foreach ( $class as $part ) {
			$class_parts[] = strtolower( $part );
		}

		return implode( '-', $class_parts );
	}

	function remove_extra_return ( $string ) {
		$lenOfSearch = strlen( "\n" );
		$posOfSearch = strrpos( $string,  "\n"  );
		return substr_replace( $string, "", $posOfSearch, $lenOfSearch );
	}
}