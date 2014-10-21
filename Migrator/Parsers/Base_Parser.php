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
	 * @var int total post to be imported
	 */
	public $total = 0;

	/**
	 * Return the list of sanitized content.
	 *
	 * @return array
	 */
	public function get_content(){
		return $this->content;
	}
} 