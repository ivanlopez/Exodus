<?php

namespace TenUp\Exodus\Schema;

abstract class Base_Schema {

	public $type;

	public $site;

	public $iterator;

	public $keys;

	abstract public function build();

	abstract public function keys();
}