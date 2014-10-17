<?php

namespace TenUp\Exodus\Migrator;

use TenUp\Exodus\Migrator\Module\Base_Importer;

class Migrator{

	protected $importer;

	protected $schema;

	function __construct( Base_Importer $importer ) {
		$this->importer = $importer;
	}

}