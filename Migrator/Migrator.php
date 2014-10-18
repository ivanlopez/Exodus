<?php

namespace TenUp\Exodus\Migrator;

interface Migrator {
	public function import();
	public function build_post_object( $content );
}