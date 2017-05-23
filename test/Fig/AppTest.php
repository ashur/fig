<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
	/**
	 * Create test fixtures
	 */
	static public function setUpBeforeClass()
	{
		$fixturesPath = dirname( dirname( __FILE__ ) ) . '/fixtures';
		$fixturesDirectory = new \Cranberry\Core\File\Directory( $fixturesPath );

		$fixturesDirectory->create();

		/* App directory */
		$appName = 'app';
		$appDirectory = $fixturesDirectory->childDir( $appName );
		$appDirectory->create();

		/* Profile */
		$profileFile = $appDirectory->child( 'profile-date.yml' );
		$profileContents = <<<PROFILE
# a test profile
---

- name: show date
  command: date
PROFILE;
		$profileFile->putContents( $profileContents );
	}

	/**
	 * Invalid $name values;
	 *
	 * @return	array
	 */
	public function invalidNameProvider()
	{
		return [
			[ [] ],
			[ (object)[] ],
			[ false ],
			[ true ],
			[ 'hello/world' ],
			[ 'hello:world' ],
			[ 'hello world' ],
		];
	}

	/**
	 *
	 */
	public function testAddProfile()
	{
		$appName = 'foo';
		$app = new App( $appName );

		$profileName = 'bar';
		$profile = new Profile( $profileName, $appName );

		$app->addProfile( $profile );

		$this->assertTrue( is_array( $app->getProfiles() ) );
		$this->assertEquals( 1, count( $app->getProfiles() ) );
		$this->assertEquals( $profile, $app->getProfile( $profileName ) );

		return $app;
	}

	/**
	 * @dataProvider	validProfileFileProvider
	 */
	public function testAddProfileFile( \Cranberry\Core\File\File $profileFile )
	{
		$appName = 'foo';
		$app = new App( $appName );

		$app->addProfileFile( $profileFile );
		$profileName = $profileFile->getBasename( '.yml' );

		$profile = $app->getProfile( $profileName );
		$this->assertEquals( $profileName, $profile->getName() );
	}

	/**
	 *
	 */
	public function testIncludedProfileInheritsVariables()
	{
		$appName = 'foo';
		$app = new App( $appName );

		/*
		 * Construct first Profile
		 */
		$profileName1 = 'profile-1';
		$profile1 = new Profile( $profileName1, $appName );

		$varMicrotime1 = microtime( true );
		$vars1 = [
			'profile' => $profileName1,
			'microtime' => $varMicrotime1,
		];

		$profile1->setVariables( $vars1 );

		/* Include $profile2 in $profile1 using an Action\Profile object */
		$includedProfileAction = new Action\Profile( ['include' => 'profile-2'] );
		$profile1->addAction( $includedProfileAction );

		$app->addProfile( $profile1 );

		/*
		 * Construct second Profile
		 */
		$profileName2 = 'profile-2';
		$profile2 = new Profile( $profileName2, $appName );

		$varString2 = 'this variable is unique to profile-2';
		$vars2 = [
			'profile' => $profileName2,
			'string' => $varString2,
		];

		$profile2->setVariables( $vars2 );

		/* Construct Command... */
		$commandActionProperties = [
			'name'		=> 'foo-command',
			'command'	=> 'echo ' . microtime( true )
		];
		$commandAction = new Action\Command( $commandActionProperties );

		$profile2->addAction( $commandAction );

		$app->addProfile( $profile2 );

		/*
		 * Action's variables should match merged array of
		 */
		$profileActions = $app->getProfileActions( $profileName1 );

		$this->assertTrue( is_array( $profileActions ) );
		$this->assertEquals( 1, count( $profileActions ) );
		$includedAction = $profileActions[0];

		$expectedVariables = [
			'profile'	=> $profileName1,
			'microtime'	=> $varMicrotime1,
			'string'	=> $varString2
		];
		$actualVariables = $includedAction->getVariables();

		$this->assertEquals( $expectedVariables, $actualVariables );
	}

	/**
	 * @dataProvider		invalidNameProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidName( $name )
	{
		$app = new App( $name );
	}

	/**
	 *
	 */
	public function testGetProfileActions()
	{
		$appName = 'foo';
		$app = new App( $appName );

		/*
		 * Construct Profile
		 */
		$profileName = 'bar';
		$profile = new Profile( $profileName, $appName );

		/* Construct Command... */
		$actionProperties = [
			'name'		=> 'foo-command',
			'command'	=> 'echo ' . time()
		];
		$commandAction = new Action\Command( $actionProperties );

		/* ...and add it to the Profile */
		$profile->addAction( $commandAction );

		/*
		 * Test whether the action is given back to us
		 */
		$app->addProfile( $profile );

		$profileActions = $app->getProfileActions( $profileName );

		$this->assertTrue( is_array( $profileActions ) );
		$this->assertEquals( 1, count( $profileActions ) );
		$this->assertEquals( $commandAction, $profileActions[0] );
	}

	/**
	 *
	 */
	public function testGetProfileActionsContainsIncludedProfileActions()
	{
		$appName = 'foo';
		$app = new App( $appName );

		/*
		 * Construct first Profile
		 */
		$profileName1 = 'profile-1';
		$profile1 = new Profile( $profileName1, $appName );

		/* Construct Command... */
		$commandActionProperties = [
			'name'		=> 'foo-command-1',
			'command'	=> 'echo ' . microtime( true )
		];
		$commandAction1 = new Action\Command( $commandActionProperties );

		/* ...and add it to the Profile */
		$profile1->addAction( $commandAction1 );

		/* Include $profile2 in $profile1 using an Action\Profile object */
		$includedProfileAction = new Action\Profile( ['include' => 'profile-2'] );
		$profile1->addAction( $includedProfileAction );

		/* Construct Command... */
		$commandActionProperties = [
			'name'		=> 'foo-command-4',
			'command'	=> 'echo ' . microtime( true )
		];
		$commandAction4 = new Action\Command( $commandActionProperties );

		/* ...and add it to the Profile */
		$profile1->addAction( $commandAction4 );

		/*
		 * Construct second Profile
		 */
		$profileName2 = 'profile-2';
		$profile2 = new Profile( $profileName2, $appName );

		/* Construct Command... */
		$commandActionProperties = [
			'name'		=> 'foo-command-2',
			'command'	=> 'echo ' . microtime(true )
		];
		$commandAction2 = new Action\Command( $commandActionProperties );

		/* ...and add it to the Profile */
		$profile2->addAction( $commandAction2 );

		/* Construct Command... */
		$commandActionProperties = [
			'name'		=> 'foo-command-3',
			'command'	=> 'echo ' . microtime(true )
		];
		$commandAction3 = new Action\Command( $commandActionProperties );

		/* ...and add it to the Profile */
		$profile2->addAction( $commandAction3 );

		/*
		 * Test which order the actions are given back to us
		 */
		$app->addProfile( $profile1 );
		$app->addProfile( $profile2 );

		$profileActions = $app->getProfileActions( $profileName1 );

		$this->assertTrue( is_array( $profileActions ) );
		$this->assertEquals( 4, count( $profileActions ) );
		$this->assertEquals( $commandAction1, $profileActions[0] );
		$this->assertEquals( $commandAction2, $profileActions[1] );
		$this->assertEquals( $commandAction3, $profileActions[2] );
		$this->assertEquals( $commandAction4, $profileActions[3] );
	}

	/**
	 * @depends				testAddProfile
	 * @expectedException	OutOfRangeException
	 */
	public function testRemoveProfile( App $app )
	{
		$profileName = 'bar';
		$app->removeProfile( $profileName );

		$this->assertTrue( is_array( $app->getProfiles() ) );
		$this->assertEquals( 0, count( $app->getProfiles() ) );
		$invalidProfile = $app->getProfile( $profileName );
	}

	/**
	 * @dataProvider		validNameProvider
	 */
	public function testValidName( $name )
	{
		$app = new App( $name );
		$this->assertEquals( $name, $app->getName() );
	}

	/**
	 * Valid $name values
	 *
	 * @return	array
	 */
	public function validNameProvider()
	{
		return [
			[ 'foo-' . time() ],
			[ 'föö-bar' ],
			[ '😋' ],
			[ time() ],
		];
	}

	/**
	 * @return	array
	 */
	public function validProfileFileProvider()
	{
		/*
		 * Hack for PHPUnit: dataProvider methods seem to be called *before*
		 *    setUpBeforeClass
		 */
		self::setUpBeforeClass();

		$fixturesPath = dirname( dirname( __FILE__ ) ) . '/fixtures';
		$fixturesDirectory = new \Cranberry\Core\File\Directory( $fixturesPath );

		$appName = 'app';
		$appDirectory = $fixturesDirectory->childDir( $appName );

		// Only .yml files
		$fileFilter = new \Cranberry\Core\File\Filter();
		$fileFilter->setDefaultMethod( \Cranberry\Core\File\Filter::METHOD_INCLUDE );
		$fileFilter->includeFileExtension( 'yml' );

		/* Profiles */
		$profileFiles = $appDirectory->children( $fileFilter );

		return [$profileFiles];
	}

	/**
	 * Tear down fixtures
	 */
	static public function tearDownAfterClass()
	{
		$fixturesPath = dirname( dirname( __FILE__ ) ) . '/fixtures';
		$fixturesDirectory = new \Cranberry\Core\File\Directory( $fixturesPath );

		$fixturesDirectory->delete();
	}
}
