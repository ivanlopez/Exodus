<?php

namespace TenUp\Exodus\Migrator\Parsers;


class Base_Parser {

	public $data;

	public $schema;

	protected $content = array();

	protected $schema_map;

	protected $schema_keys;

	public $total = 0;

	public function get_content(){
		return $this->content;
	}
} 