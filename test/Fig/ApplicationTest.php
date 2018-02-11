<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use FigTest\TestCase;

class ApplicationTest extends TestCase
{
	/* Helpers */

	public function createObject()
	{
		$application = new Application();
		return $application;
	}

	/* Providers */


	/* Tests */

	public function test_addRepository()
	{
		$application = $this->createObject();

		$repoName = getUniqueString( 'repo' );
		$repository = new Repository( $repoName );

		$this->assertFalse( $application->hasRepository( $repoName ) );

		$application->addRepository( $repository );

		$this->assertTrue( $application->hasRepository( $repoName ) );
	}

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

	public function test_hasRepository()
	{
		$application = $this->createObject();
		$repoName = getUniqueString( 'repo' );

		$this->assertFalse( $application->hasRepository( $repoName ) );
	}
}
