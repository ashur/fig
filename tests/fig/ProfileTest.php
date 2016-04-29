<?php

/*
 * This file is part of the Fig test suite
 */

use Huxtable\Core\File;

class ProfileTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @return	void
	 */
	public function setUp()
	{
		$pathFixtures = __DIR__ . '/fixtures';

		$this->dirFixtures = new File\Directory( $pathFixtures );
		$this->dirTemp = $this->dirFixtures->childDir( 'temp' );
	}

	/**
	 * @dataProvider	profileInstanceProvider
	 */
	public function testCreation( $profile, $profileName )
	{
		$this->assertEquals( $profileName, $profile->getName() );
	}

	/**
	 * @dataProvider	profileInstanceProvider
	 */
	public function testAddCommands( $profile )
	{
		$preCommand = 'precommand_' . rand( 0, 999 );
		$postCommand = 'postcommand_' . rand( 0, 999 );

		$profile->addPreCommand( $preCommand );
		$profile->addPostCommand( $postCommand );

		$commands = $profile->getCommands();

		$this->assertEquals( $preCommand, $commands['pre'][0] );
		$this->assertEquals( $postCommand, $commands['post'][0] );
	}

	public function testCommandsPopulatedFromConfiguration()
	{
		$dirProfile = $this->dirFixtures
		->childDir( '.fig' )
		->childDir( 'example-app' )
		->childDir( 'profile-command-test' );

		$profile = Fig\Profile::getInstanceFromDirectory( $dirProfile );

		// Read commands from config file
		$profileConfigContents = $dirProfile
		->child( 'config.json' )
		->getContents();

		$profileConfigData = json_decode( $profileConfigContents, true );
		$commands = $profile->getCommands();

		$this->assertEquals( $profileConfigData['commands']['pre'][0], $commands['pre'][0] );
		$this->assertEquals( $profileConfigData['commands']['post'][0], $commands['post'][0] );
	}

	public function testAssetsPopulatedFromConfiguration()
	{
		// Create Profile
		$profileName = 'profile_' . rand( 0, 499 );
		$appName = 'app_' . rand( 0, 999 );

		$dirProfile = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName )
		->childDir( $profileName );
		$dirProfile->mkdir( 0777, true );

		// Create and add Asset
		$assetTarget = $this->dirTemp->childDir( 'dir-target' );
		$assetTarget->mkdir( 0777, true );
		$asset = new Fig\Asset( $assetTarget );

		$assetSource = $this->dirTemp
		->childDir( 'assets' )
		->childDir( 'dir-source' );
		$asset->replaceWith( $assetSource );

		// Set up config.json
		$configFile = $dirProfile->child( 'config.json' );
		$configData['files'][] = $asset;
		$configContents = json_encode( $configData );
		$configFile->putContents( $configContents );

		$profile = Fig\Profile::getInstanceFromDirectory( $dirProfile );
		$assets = $profile->getAssets();

		$dirExpected = $dirProfile
		->childDir( 'assets' )
		->child( 'dir-source' );

		$dirActual = $assets[0]->getSource();

		$this->assertEquals( $dirExpected, $dirActual );
	}

	/**
	 * @expectedException	Exception
	 */
	public function testDirectoryMissingConfigThrowsException()
	{
		$profileName = 'profile_' . rand( 0, 499 );
		$appName = 'app_' . rand( 0, 999 );

		$dirProfile = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName )
		->childDir( $profileName );

		$profile = Fig\Profile::getInstanceFromDirectory( $dirProfile );
	}

	/**
	 * @expectedException	Exception
	 */
	public function testMalformedConfigThrowsException()
	{
		// Set up dummy profile source
		$parentProfileName = 'profile_' . rand( 500, 999 );
		$profileName = 'profile_' . rand( 0, 499 );

		$configData['extends'] = $parentProfileName;
		$configJSON = serialize( $configData );	// Not JSON encoded

		$appName = 'app_' . rand( 0, 999 );
		$dirProfile = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName )
		->childDir( $profileName );

		$dirProfile->mkdir( 0777, true );

		$fileConfig = $dirProfile->child( 'config.json' );
		$fileConfig->putContents( $configJSON );

		$profile = Fig\Profile::getInstanceFromDirectory( $dirProfile );
	}

	public function testExtendsConfigProperty()
	{
		// Set up dummy profile source
		$parentProfileName = 'profile_' . rand( 500, 999 );
		$profileName = 'profile_' . rand( 0, 499 );

		$configData['extends'] = $parentProfileName;
		$configJSON = json_encode( $configData );

		$appName = 'app_' . rand( 0, 999 );
		$dirProfile = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName )
		->childDir( $profileName );

		$dirProfile->mkdir( 0777, true );

		$fileConfig = $dirProfile->child( 'config.json' );
		$fileConfig->putContents( $configJSON );

		$profile = Fig\Profile::getInstanceFromDirectory( $dirProfile );

		// Test
		$this->assertEquals( $parentProfileName, $profile->getParentName() );
	}

	public function testExtendWith()
	{
		// Set up dummy profiles
		$parentProfileName = 'profile_' . rand( 0, 499 );
		$profileName = 'profile_' . rand( 500, 999 );

		$appName = 'app_' . rand( 0, 999 );
		$dirApp = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName );

		$dirParentProfile = $dirApp->childDir( $parentProfileName );
		$dirProfile = $dirApp->childDir( $profileName );

		$parentProfile = new Fig\Profile( $parentProfileName );
		$partialProfile = new Fig\Profile( $profileName );

		// Commands
		$parentPreCommand = 'pre_command_parent';
		$parentPostCommand = 'post_command_parent';
		$preCommand = 'pre_command_child';
		$postCommand = 'post_command_child';

		// Assets
		$parentAssetSource = $this->dirTemp
		->childDir( 'source' )
		->child( 'parent.php' );
		$parentAssetTarget = $this->dirTemp
		->childDir( 'target' )
		->child( 'parent.php' );
		$parentAsset = new Fig\Asset( $parentAssetSource, $parentAssetTarget );

		$childAssetSource = $this->dirTemp
		->childDir( 'source' )
		->child( 'child.php' );
		$childAssetTarget = $this->dirTemp
		->childDir( 'target' )
		->child( 'child.php' );
		$childAsset = new Fig\Asset( $childAssetSource, $childAssetTarget );

		// Configure profiles
		$parentProfile
		->addPreCommand( $parentPreCommand )
		->addPostCommand( $parentPostCommand )
		->addAsset( $parentAsset );

		$partialProfile
		->addPreCommand( $preCommand )
		->addPostCommand( $postCommand )
		->addAsset( $childAsset );

		// Test
		$profile = $parentProfile->extendWith( $partialProfile );
		$commands = $profile->getCommands();
		$assets = $profile->getAssets();

		$this->assertEquals( $parentPreCommand, $commands['pre'][0] );
		$this->assertEquals( $preCommand, $commands['pre'][1] );
		$this->assertEquals( $parentPostCommand, $commands['post'][0] );
		$this->assertEquals( $postCommand, $commands['post'][1] );

		$this->assertEquals( $parentAsset, $assets[0] );
		$this->assertEquals( $childAsset, $assets[1] );
	}

	public function testExtendSelf()
	{
		// Set up dummy profiles
		$profileName = 'profile_' . rand( 500, 999 );

		$appName = 'app_' . rand( 0, 999 );
		$dirApp = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName );

		$dirProfile = $dirApp->childDir( $profileName );
		$profile = new Fig\Profile( $profileName );

		// Commands
		$preCommand = 'pre_command_child';
		$postCommand = 'post_command_child';

		// Assets
		$assetSource = $this->dirTemp
		->childDir( 'source' )
		->child( 'child.php' );
		$assetTarget = $this->dirTemp
		->childDir( 'target' )
		->child( 'child.php' );
		$asset = new Fig\Asset( $assetSource, $assetTarget );

		// Configure profiles
		$profile
		->addPreCommand( $preCommand )
		->addPostCommand( $postCommand )
		->addAsset( $asset )
		->setParentName( $profileName );

		// Test
		$extendedProfile = $profile->extendWith( $profile );

		$expectedProfile = clone $profile;
		$expectedProfile->setParentName( null );

		$this->assertEquals( $expectedProfile, $extendedProfile );
	}

	/**
	 * @dataProvider	profileInstanceProvider
	 */
	public function testWriteToDisk( $profileExpected, $profileName )
	{
		$appName = 'app_' . rand( 0, 999 );

		$dirProfile = $this->dirTemp
		->childDir( '.fig' )
		->childDir( $appName )
		->childDir( $profileName );

		// Commands
		$profileExpected->addPreCommand( 'pre_command_' . rand( 0, 99 ) );
		$profileExpected->addPostCommand( 'post_command_' . rand( 99, 199 ) );

		// Assets
		$assetSource = $this->dirTemp
		->childDir( 'source' )
		->child( 'child.php' );
		$assetTarget = $this->dirTemp
		->childDir( 'target' )
		->child( 'child.php' );
		$asset = new Fig\Asset( $assetSource, $assetTarget );

		$profileExpected->addAsset( $asset );
		$profileExpected->write( $dirProfile );

		$profileActual = Fig\Profile::getInstanceFromDirectory( $dirProfile );

		$this->assertEquals( $profileExpected, $profileActual );
	}

	/**
	 * @return	array
	 */
	public function profileInstanceProvider()
	{
		$profileName = 'profile_' . rand( 0, 999 );
		$profile = new Fig\Profile( $profileName );

		return [
			[$profile, $profileName]
		];
	}

	public function tearDown()
	{
		if( $this->dirTemp->exists() )
		{
			$this->dirTemp->rmDir( true );
		}
	}
}
