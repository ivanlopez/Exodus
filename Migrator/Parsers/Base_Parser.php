<?php

namespace TenUp\Exodus\Migrator\Parsers;


/**
 * Class Base_Parser
 * @package TenUp\Exodus\Migrator\Parsers
 */
abstract class Base_Parser {

	/**
	 * @var array raw data being imported;
	 */
	public $data;

	/**
	 * @var Base_Schema instance of the injected schema
	 */
	public $schema;

	/**
	 * @var array sanitized content ready for import
	 */
	protected $content = array();

	/**
	 * @var array list of schema maps for each passed post type
	 */
	protected $schema_map;

	/**
	 * @var array list of schema keys for mapping post types
	 */
	protected $schema_keys;

	/**
	 * Return the list of sanitized content.
	 *
	 * @return array
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Return the number of content pieces to be imported
	 *
	 * @return int
	 */
	public function get_content_count() {
		return count( $this->content );
	}

	/**
	 * Creates a user object
	 *
	 * @param $author
	 *
	 * @return \stdClass
	 */
	protected function setup_author( $author ) {
		$user = new \stdClass();

		if ( empty( $author ) || is_null( $author ) ) {
			return '';
		}

		if ( is_email( $author ) ) {
			$user->user_email = $author;
			$user->user_login = $author;
		} else {
			$name             = explode( ' ', $author );
			$user->first_name = $name[0];
			$user->last_name  = $name[1];
			$user->user_login = $author;
			$user->user_email = '';
		}

		return $user;
	}
} 