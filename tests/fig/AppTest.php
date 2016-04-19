<?php

/*
 * This file is part of the Fig test suite
 */

use Huxtable\Core\File;

class AppTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @return	void
	 */
	public function setUp()
	{
		$pathFixtures = __DIR__ . '/fixtures';

		$this->dirFixtures = new File\Directory( $pathFixtures );
		$this->dirTemp = $this->dirFixtures->childDir( 'temp' );
		$this->dirFig = $this->dirTemp->childDir( '.fig' );
	}

	/**
	 * @dataProvider	appInstanceProvider
	 */
	public function testCreation( Fig\App $app, $appName )
	{
		$this->assertEquals( $appName, $app->getName() );
	}

	/**
	 * @dataProvider	appInstanceProvider
	 */
	public function testAddCommand( Fig\App $app )
	{
		$command = new Fig\Command( 'bar', 'echo bar' );

		$app->addCommand( $command );
		$this->assertEquals( $command, $app->getCommand( 'bar' ) );
	}

	/**
	 * @dataProvider		appInstanceProvider
	 * @expectedException	OutOfRangeException
	 */
	public function testGetInvalidCommandThrowsException( Fig\App $app )
	{
		$app->getCommand( 'bar' );
	}

	/**
	 * @dataProvider	appInstanceProvider
	 */
	public function testAddProfile( Fig\App $app, $appName )
	{
		$profileName = 'profile_' . rand( 500, 999 );
		$profile = new Fig\Profile( $profileName );

		$app->addProfile( $profile );
		$profiles = $app->getProfiles();

		$this->assertEquals( $profile, $profiles[$profileName] );
		$this->assertEquals( $profile, $app->getProfile( $profileName ) );
	}

	/**
	 * @dataProvider	appInstanceProvider
	 */
	public function testAddExtendedProfile( Fig\App $app, $appName )
	{
		// Child profile
		$childProfileName = 'profile_' . rand( 0, 499 );
		$childProfile = new Fig\Profile( $childProfileName );
		$childPreCommandName = "pre_{$childProfileName}";
		$childPostCommandName = "post_{$childProfileName}";
		$childProfile->addPreCommand( $childPreCommandName );
		$childProfile->addPostCommand( $childPostCommandName );

		// Parent profile
		$parentProfileName = 'profile_' . rand( 0, 499 );
		$parentProfile = new Fig\Profile( $parentProfileName );
		$parentPreCommandName = "pre_{$parentProfileName}";
		$parentPostCommandName = "post_{$parentProfileName}";
		$parentProfile->addPreCommand( $parentPreCommandName );
		$parentProfile->addPostCommand( $parentPostCommandName );

		$childProfile->setParentName( $parentProfileName );

		$app->addProfile( $childProfile );
		$app->addProfile( $parentProfile );
		$extendedProfile = $app->getProfile( $childProfileName );

		$commands = $extendedProfile->getCommands();
		$this->assertEquals( $commands['pre'][0], $parentPreCommandName );
		$this->assertEquals( $commands['post'][0], $parentPostCommandName );
		$this->assertEquals( $commands['pre'][1], $childPreCommandName );
		$this->assertEquals( $commands['post'][1], $childPostCommandName );
	}


	/**
	 * @dataProvider		appInstanceProvider
	 * @expectedException	OutOfRangeException
	 */
	public function testGetInvalidProfileThrowsException( Fig\App $app )
	{
		$app->getProfile( 'bar' );
	}

	public function testGetInstanceFromDirectory()
	{
		$appName = 'example-app';
		$profileName = 'profile-command-test';

		$appDir = $this->dirFixtures
		->childDir( '.fig' )
		->childDir( $appName );

		$profileDir = $appDir
		->childDir( $profileName );

		$app = Fig\App::getInstanceFromDirectory( $appDir );

		// Profile
		$profile = Fig\Profile::getInstanceFromDirectory( $profileDir );
		$this->assertEquals( $profile, $app->getProfile( $profileName ) );

		// Commands
		$commandHello = $app->getCommand( 'hello' );
		$this->assertEquals( 'echo hello', $commandHello->getCommand() );

		$commandGoodbye = $app->getCommand( 'goodbye' );
		$this->assertEquals( 'echo goodbye', $commandGoodbye->getCommand() );
	}

	/**
	 * @dataProvider	appInstanceProvider
	 */
	public function testWrite( Fig\App $appExpected, $appName )
	{
		// Command
		$command = new Fig\Command( 'bar', 'echo bar' );
		$appExpected->addCommand( $command );

		// Profile
		$profileName = 'profile_' . rand( 500, 999 );
		$profile = new Fig\Profile( $profileName );
		$profile->addPreCommand( 'bar' );
		$appExpected->addProfile( $profile );

		$dirApp = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName );

		$appExpected->write( $dirApp );

		// Test
		$appActual = Fig\App::getInstanceFromDirectory( $dirApp );
		$this->assertEquals( $appExpected, $appActual );
	}

	/**
	 * @return	array
	 */
	public function appInstanceProvider()
	{
		$appName = 'app_' . rand( 0, 499 );
		$app = new Fig\App( $appName );

		return [
			[$app, $appName]
		];
	}

	/**
	 * @return	void
	 */
	public function tearDown()
	{
		if( $this->dirTemp->exists() )
		{
			$this->dirTemp->rmDir( true );
		}
	}
}
