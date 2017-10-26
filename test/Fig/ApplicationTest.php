<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
	public function test_const_NAME()
	{
		$this->assertTrue( is_string( Application::NAME ) );
	}

	public function test_const_VERSION()
	{
		$this->assertTrue( is_string( Application::VERSION ) );
		$this->assertTrue( version_compare( Application::VERSION, '0.0', '>=' ) );
	}

	public function test_const_PHP_MIN()
	{
		$this->assertTrue( is_string( Application::PHP_MIN ) );
		$this->assertTrue( version_compare( Application::PHP_MIN, '0.0', '>=' ) );
	}
}
