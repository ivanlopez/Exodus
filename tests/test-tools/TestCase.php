<?php

namespace TenUp\Exodus;

use PHPUnit_Framework_TestResult;
use Text_Template;
use WP_Mock;
use WP_Mock\Tools\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {

	/**
	 * Wrapper for calling protected methods in your test
	 *
	 * @param       $object
	 * @param       $methodName
	 * @param array $parameters
	 *
	 * @return mixed
	 */
	public function invoke_protected_method( &$object, $methodName, array $parameters = array() ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method     = $reflection->getMethod( $methodName );
		$method->setAccessible( true );
		return $method->invokeArgs( $object, $parameters );
	}
}