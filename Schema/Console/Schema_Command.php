<?php

namespace TenUp\Exodus\Schema\Console;

/**
 * Class Schema_Command
 * @package TenUp\Exodus\Schema\Console
 */
class Schema_Command {

	/**
	 * @var string directory where migration files live
	 */
	protected $directory;

	/**
	 * Initiate the schema command and runs the create_migration_directory method
	 */
	function __construct( $directory ) {
		$this->directory = $directory ;
		$this->create_migration_directory();
	}

	/**
	 * Checks to see if a migrations folder exist in the wp-content
	 * directory. If it does not exist than it created it.
	 */
	protected function create_migration_directory() {
		if ( ! file_exists( $this->directory ) ) {
			mkdir( $this->directory, 0755, false );
		}
	}

	/**
	 * Generate a new migration file in the migrations folder.
	 *
	 * @param array $args
	 */
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

		if ( isset( $args['verify'] ) ) {
			$propertied .= "	public \$verify = " . $args['verify'] . ";\n\n";
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

		$migration = fopen( $this->directory . self::sanitize_file_name( $args['name'] ) . '.php', "w" );
		fwrite( $migration, $stub );
		fclose( $migration );
	}

	/**
	 * Returns a sanitized class name following WordPress standards.
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function sanitize_class_name( $name ) {
		$class_parts = array();
		$class       = preg_replace( '#[^a-zA-Z_]#', '', $name );
		$class       = explode( '_', $name );
		foreach ( $class as $part ) {
			$class_parts[] = ucwords( $part );
		}

		return implode( '_', $class_parts );
	}

	/**
	 * Returns a sanitized file name.
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public static function sanitize_file_name( $name ) {
		$class_parts = array();
		$class       = preg_replace( '#[^a-zA-Z_]#', '', $name );
		$class       = explode( '_', $name );
		foreach ( $class as $part ) {
			$class_parts[] = strtolower( $part );
		}

		return implode( '-', $class_parts );
	}

	/**
	 * Removed extra hard return created during string builds.
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	function remove_extra_return ( $string ) {
		$lenOfSearch = strlen( "\n" );
		$posOfSearch = strrpos( $string,  "\n"  );
		return substr_replace( $string, "", $posOfSearch, $lenOfSearch );
	}
}