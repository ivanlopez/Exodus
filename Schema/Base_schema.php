<?php

namespace TenUp\Exodus\Schema;

/**
 * Class Base_Schema
 * @package TenUp\Exodus\Schema
 */
abstract class Base_Schema {

	/**
	 * @var string type of schema being created. Supports json, xml, sql
	 */
	public $type;

	/**
	 * @var int the id to migrate the data to
	 */
	public $site;

	/**
	 * @var string the nesting of where the post are in your import file
	 */
	public $iterator;

	/**
	 * @var string name of the url parameter in your import file in order to export a csv of old to new urls
	 */
	public $keys;

	/**
	 * Returns the schema for current migration.
	 *
	 * @return mixed
	 */
	abstract public function build();

	/**
	 * Returns a list of the post type keys being mapped.
	 *
	 * @return mixed
	 */
	abstract public function keys();
}