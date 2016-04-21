<?php

/*
 * This file is part of the Fig test suite
 */

use Huxtable\Core\File;

class FigTest extends PHPUnit_Framework_TestCase
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
	public function testAddApp( $app, $appName )
	{
		$fig = new Fig\Fig( $this->dirFig );
		$fig->addApp( $app );

		$apps = $fig->getApps();
		$this->assertTrue( is_array( $apps ) );
		$this->assertEquals( $app, $apps[0] );
		$this->assertEquals( $app, $fig->getApp( $appName ) );
	}

	/**
	 * @expectedException	OutOfRangeException
	 */
	public function testGetInvalidAppThrowsException()
	{
		$fig = new Fig\Fig( $this->dirFig );
		$fig->getApp( 'foo' );
	}

	/**
	 * @dataProvider	appInstanceProvider
	 */
	public function testAppInstantiatedFromDisk( $app, $appName )
	{
		$dirApp = $this->dirFig->childDir( $appName );
		$app->write( $dirApp );

		// Test
		$fig = new Fig\Fig( $this->dirFig );
		$apps = $fig->getApps();

		$this->assertTrue( is_array( $apps ) );
		$this->assertEquals( $app, $apps[0] );
		$this->assertEquals( $app, $fig->getApp( $appName ) );
	}

	/**
	 * @dataProvider	appInstanceProvider
	 */
	public function testDeployAppProfile( $app, $appName )
	{
		// Setup
		$preCommandName = 'command_' . rand( 1000, 1499 ) . '_pre';
		$filePre = $this->dirTemp->child( "{$preCommandName}.txt" );
		$preCommandString = "touch {$filePre}";
		$preCommand = new Fig\Command( $preCommandName, $preCommandString );

		$postCommandName = 'command_' . rand( 1000, 1499 ) . '_post';
		$filePost = $this->dirTemp->child( "{$postCommandName}.txt" );
		$postCommandString = "touch {$filePost}";
		$postCommand = new Fig\Command( $postCommandName, $postCommandString );

		$profileName = 'profile_' . rand( 500, 999 );
		$profile = new Fig\Profile( $profileName );

		$profile->addPreCommand( $preCommandName );
		$profile->addPostCommand( $postCommandName );

		$app->addCommand( $preCommand );
		$app->addCommand( $postCommand );
		$app->addProfile( $profile );

		// Tests
		$fig = new Fig\Fig( $this->dirFig );
		$fig->addApp( $app );

		$this->assertFalse( $filePre->exists() );
		$this->assertFalse( $filePost->exists() );

		$fig->deployProfile( $appName, $profileName );

		$this->assertTrue( $filePre->exists() );
		$this->assertTrue( $filePost->exists() );
	}

	/**
	 * @dataProvider		appInstanceProvider
	 * @expectedException	Exception
	 */
	public function testDeployCommandErrorThrowsException( $app, $appName )
	{
		// Setup
		$preCommandName = 'command_' . rand( 1000, 1499 ) . '_pre';
		$preCommandString = "exit 1";
		$preCommand = new Fig\Command( $preCommandName, $preCommandString );

		$app->addCommand( $preCommand );

		$profileName = 'profile_' . rand( 500, 999 );
		$profile = new Fig\Profile( $profileName );
		$profile->addPreCommand( $preCommandName );

		$app->addProfile( $profile );

		// Tests
		$fig = new Fig\Fig( $this->dirFig );
		$fig->addApp( $app );
		$fig->deployProfile( $appName, $profileName );
	}

	/**
	 * @dataProvider		appInstanceProvider
	 */
	public function testExecuteCommand( $app, $appName )
	{
		// Setup
		$preCommandName = 'command_' . rand( 1000, 1499 ) . '_pre';
		$filePre = $this->dirTemp->child( "{$preCommandName}.txt" );
		$preCommandString = "touch {$filePre}";
		$preCommand = new Fig\Command( $preCommandName, $preCommandString );

		$app->addCommand( $preCommand );

		// Tests
		$fig = new Fig\Fig( $this->dirFig );
		$fig->addApp( $app );

		$this->assertFalse( $filePre->exists() );
		$fig->executeCommand( $appName, $preCommandName );
		$this->assertTrue( $filePre->exists() );
	}

	/**
	 * @dataProvider		appInstanceProvider
	 */
	public function testDeployCreateAssets( $app, $appName )
	{
		$dirTarget = $this->dirFig->childDir( 'dest' );
		$dirTarget->mkdir( 0777, true );

		$assetName = 'asset_' . rand( 0, 499 ) . '.php';
		$assetTarget = $dirTarget->child( $assetName );

		$asset = new Fig\Asset( $assetTarget );
		$asset->create();

		$profileName = 'profile_' . rand( 500, 999 );
		$profile = new Fig\Profile( $profileName );
		$profile->addAsset( $asset );

		$app->addProfile( $profile );

		// Tests
		$fig = new Fig\Fig( $this->dirFig );
		$fig->addApp( $app );
		$fig->deployProfile( $appName, $profileName );

		$this->assertTrue( $assetTarget->exists() );
	}

	/**
	 * @dataProvider		appInstanceProvider
	 */
	public function testDeployDeleteAssets( $app, $appName )
	{
		$dirTarget = $this->dirFig->childDir( 'dest' );
		$dirTarget->mkdir( 0777, true );

		$assetName = 'asset_' . rand( 0, 499 ) . '.php';
		$assetTarget = $dirTarget->child( $assetName );
		$assetTarget->create();

		$this->assertTrue( $assetTarget->exists() );

		$asset = new Fig\Asset( $assetTarget );
		$asset->delete();

		$profileName = 'profile_' . rand( 500, 999 );
		$profile = new Fig\Profile( $profileName );
		$profile->addAsset( $asset );

		$app->addProfile( $profile );

		// Tests
		$fig = new Fig\Fig( $this->dirFig );
		$fig->addApp( $app );
		$fig->deployProfile( $appName, $profileName );

		$this->assertFalse( $assetTarget->exists() );
	}

	/**
	 * @return	array
	 */
	public function appInstanceProvider()
	{
		$appName = 'app_' . rand( 0, 499 );
		$app = new Fig\App( $appName );

		$profileName = 'profile_' . rand( 500, 999 );
		$profile = new Fig\Profile( $profileName );

		$commandName = 'command_' . rand( 1000, 1499 );
		$commandString = "echo {$commandName}";
		$command = new Fig\Command( $commandName, $commandString );

		$profile->addPreCommand( $commandName );

		$app->addCommand( $command );
		$app->addProfile( $profile );

		return [
			[$app, $appName]
		];
	}

	public function tearDown()
	{
		if( $this->dirTemp->exists() )
		{
			$this->dirTemp->delete();
		}
	}
}
