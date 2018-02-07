<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use FigTest\TestCase;

class RepositoryTest extends TestCase
{
	/* Helpers */

	public function createObject()
	{
		$name = getUniqueString( 'repo-' );

		$repository = new Repository( $name );

		return $repository;
	}

	public function createObject_fromName( string $name ) : Repository
	{
		$repository = new Repository( $name );

		return $repository;
	}


	/* Providers */



	/* Tests */

	public function test_addProfile()
	{
		$repo = $this->createObject();

		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		$this->assertFalse( $repo->hasProfile( $profileName ) );

		$repo->addProfile( $profile );

		$this->assertTrue( $repo->hasProfile( $profileName ) );
	}

	public function test_getName()
	{
		$name = getUniqueString( 'repo-' );
		$repo = $this->createObject_fromName( $name );

		$this->assertEquals( $name, $repo->getName() );
	}

	public function test_getProfile()
	{
		$repo = $this->createObject();

		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		$repo->addProfile( $profile );

		$this->assertEquals( $profile, $repo->getProfile( $profileName ) );
	}

	public function test_getProfileActions()
	{
		$repo = $this->createObject();

		/* Construct profile with actions */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		$commandAction = new Action\Shell\CommandAction( 'say hello', 'echo', ['hello'] );
		$profile->addAction( $commandAction );

		$repo->addProfile( $profile );

		$this->assertTrue( $repo->hasProfile( $profileName ) );

		$this->assertEquals( [$commandAction], $repo->getProfileActions( $profileName ) );
	}

	public function test_getProfileActions_whichExtendsProfile()
	{
		$repo = $this->createObject();

		/* Construct profile to be extended */
		$extendedProfileName = getUniqueString( 'extended-profile-' );
		$extendedProfile = new Profile( $extendedProfileName );

		$commandAction = new Action\Shell\CommandAction( 'say hello', 'echo', ['hello'] );
		$extendedProfile->addAction( $commandAction );

		$sourcePath = getUniqueString( 'file-' );
		$targetPath = getUniqueString( '/usr/local/foo/file-' );
		$replaceFileAction = new Action\Filesystem\ReplaceFileAction( 'replace a file', $sourcePath, $targetPath );
		$extendedProfile->addAction( $replaceFileAction );

		/* Construct profile which extends a profile */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		$extendAction = new Action\Meta\ExtendAction( $extendedProfileName );
		$profile->addAction( $extendAction );

		/* Add both profiles to repo */
		$repo->addProfile( $profile );
		$repo->addProfile( $extendedProfile );

		$this->assertTrue( $repo->hasProfile( $profileName ) );
		$this->assertTrue( $repo->hasProfile( $extendedProfileName ) );

		$profileActions = $repo->getProfileActions( $profileName );

		$this->assertEquals( 2, count( $profileActions ) );

		foreach( $profileActions as $profileAction )
		{
			$this->assertEquals( $profileName, $profileAction->getProfileName() );
		}
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::PROFILE_NOT_FOUND
	 */
	public function test_getProfileActions_whichExtendsUndefinedProfile_throwsException()
	{
		$repo = $this->createObject();

		/* Construct profile which extends an undefined second profile */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		/* Action to extend undefined profile */
		$undefinedProfileName = getUniqueString( 'undefined-profile-' );
		$extendAction = new Action\Meta\ExtendAction( $undefinedProfileName );

		$profile->addAction( $extendAction );

		$repo->addProfile( $profile );

		$this->assertTrue( $repo->hasProfile( $profileName ) );

		$repo->getProfileActions( $profileName );
	}

	public function test_getProfileActions_whichIncludesProfile()
	{
		$repo = $this->createObject();

		/* Construct profile to be included */
		$includedProfileName = getUniqueString( 'included-profile-' );
		$includedProfile = new Profile( $includedProfileName );

		$commandAction = new Action\Shell\CommandAction( 'say hello', 'echo', ['hello'] );

		$includedProfile->addAction( $commandAction );

		/* Construct profile which includes a profile */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		$includeAction = new Action\Meta\IncludeAction( $includedProfileName );
		$profile->addAction( $includeAction );

		/* Add both profiles to repo */
		$repo->addProfile( $profile );
		$repo->addProfile( $includedProfile );

		$this->assertTrue( $repo->hasProfile( $profileName ) );
		$this->assertTrue( $repo->hasProfile( $includedProfileName ) );

		$profileActions = $repo->getProfileActions( $profileName );

		$this->assertEquals( [$commandAction], $repo->getProfileActions( $profileName ) );
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::PROFILE_NOT_FOUND
	 */
	public function test_getProfileActions_whichIncludesUndefinedProfile_throwsException()
	{
		$repo = $this->createObject();

		/* Construct profile which includes an undefined second profile */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		/* Action to include undefined profile */
		$undefinedProfileName = getUniqueString( 'undefined-profile-' );
		$includeAction = new Action\Meta\IncludeAction( $undefinedProfileName );

		$profile->addAction( $includeAction );

		$repo->addProfile( $profile );

		$this->assertTrue( $repo->hasProfile( $profileName ) );

		$repo->getProfileActions( $profileName );
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::PROFILE_NOT_FOUND
	 */
	public function test_getProfile_withUndefinedProfileName_throwsException()
	{
		$repo = $this->createObject();

		$profileName = getUniqueString( 'profile-' );

		$repo->getProfile( $profileName );
	}

	public function test_getProfileVars()
	{
		$repo = $this->createObject();

		/* Construct profile with vars */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		$vars = ['time' => microtime( true ), 'foo' => getUniqueString( 'foo-' ) ];
		$profile->setVars( $vars );

		$repo->addProfile( $profile );

		$this->assertEquals( $vars, $repo->getProfileVars( $profileName ) );
	}

	public function test_getProfileVars_withDeeplyNestedProfiles()
	{
		$repo = $this->createObject();

		/* profile-1 */
		$profileName_1 = 'profile-1';
		$profile_1 = new Profile( $profileName_1 );

		$profileVars_1['1-only'] = getUniqueString( 'one' );
		$profileVars_1['2-up'] = getUniqueString( 'one' );
		$profileVars_1['3-up'] = getUniqueString( 'one' );
		$profileVars_1['4-up'] = getUniqueString( 'one' );

		$profile_1->setVars( $profileVars_1 );

		/* profile-2 */
		$profileName_2 = 'profile-2';
		$profile_2 = new Profile( $profileName_2 );

		$profileVars_2['2-only'] = getUniqueString( 'two' );
		$profileVars_2['2-up'] = getUniqueString( 'two' );
		$profileVars_2['3-up'] = getUniqueString( 'two' );
		$profileVars_2['4-up'] = getUniqueString( 'two' );

		$profile_2->setVars( $profileVars_2 );

		/* profile-3 */
		$profileName_3 = 'profile-3';
		$profile_3 = new Profile( $profileName_3 );

		$profileVars_3['3-only'] = getUniqueString( 'three' );
		$profileVars_3['3-up'] = getUniqueString( 'three' );
		$profileVars_3['4-up'] = getUniqueString( 'three' );

		$profile_3->setVars( $profileVars_3 );

		/* profile-4 */
		$profileName_4 = 'profile-4';
		$profile_4 = new Profile( $profileName_4 );

		$profileVars_4['4-only'] = getUniqueString( 'four' );
		$profileVars_4['4-up'] = getUniqueString( 'four' );

		$profile_4->setVars( $profileVars_4 );

		/* Define inclusion */
		$profile_3->addAction( new Action\Meta\ExtendAction( $profileName_4 ) );
		$profile_2->addAction( new Action\Meta\IncludeAction( $profileName_3 ) );
		$profile_1->addAction( new Action\Meta\ExtendAction( $profileName_2 ) );

		/* Add all profiles to repo */
		$repo->addProfile( $profile_1 );
		$repo->addProfile( $profile_2 );
		$repo->addProfile( $profile_3 );
		$repo->addProfile( $profile_4 );

		$this->assertTrue( $repo->hasProfile( $profileName_1 ) );
		$this->assertTrue( $repo->hasProfile( $profileName_2 ) );
		$this->assertTrue( $repo->hasProfile( $profileName_3 ) );
		$this->assertTrue( $repo->hasProfile( $profileName_4 ) );

		$expectedVars = [
			'1-only' => $profileVars_1['1-only'],

			'2-only' => $profileVars_2['2-only'],
			'2-up'   => $profileVars_1['2-up'],

			'3-only' => $profileVars_3['3-only'],
			'3-up'   => $profileVars_1['3-up'],

			'4-only' => $profileVars_4['4-only'],
			'4-up'   => $profileVars_1['4-up'],
		];

		$actualVars = $repo->getProfileVars( $profileName_1 );

		ksort( $expectedVars );
		ksort( $actualVars );

		$this->assertEquals( $expectedVars, $actualVars );
	}

	public function test_getProfileVars_withExtendedProfile()
	{
		$repo = $this->createObject();

		/* profile-1 */
		$profileName_1 = 'profile-1';
		$profile_1 = new Profile( $profileName_1 );

		$profileVars_1['1-only'] = getUniqueString( 'one' );
		$profileVars_1['2-up'] = getUniqueString( 'one' );

		$profile_1->setVars( $profileVars_1 );

		/* profile-2 */
		$profileName_2 = 'profile-2';
		$profile_2 = new Profile( $profileName_2 );

		$profileVars_2['2-only'] = getUniqueString( 'two' );
		$profileVars_2['2-up'] = getUniqueString( 'two' );

		$profile_2->setVars( $profileVars_2 );

		/* Define inclusion */
		$profile_1->addAction( new Action\Meta\ExtendAction( $profileName_2 ) );

		/* Add all profiles to repo */
		$repo->addProfile( $profile_1 );
		$repo->addProfile( $profile_2 );

		$this->assertTrue( $repo->hasProfile( $profileName_1 ) );
		$this->assertTrue( $repo->hasProfile( $profileName_2 ) );

		$expectedVars = [
			'1-only' => $profileVars_1['1-only'],

			'2-only' => $profileVars_2['2-only'],
			'2-up'   => $profileVars_1['2-up'],
		];

		$actualVars = $repo->getProfileVars( $profileName_1 );

		ksort( $expectedVars );
		ksort( $actualVars );

		$this->assertEquals( $expectedVars, $actualVars );
	}

	public function test_getProfileVars_withIncludedProfile()
	{
		$repo = $this->createObject();

		/* profile-1 */
		$profileName_1 = 'profile-1';
		$profile_1 = new Profile( $profileName_1 );

		$profileVars_1['1-only'] = getUniqueString( 'one' );
		$profileVars_1['2-up'] = getUniqueString( 'one' );

		$profile_1->setVars( $profileVars_1 );

		/* profile-2 */
		$profileName_2 = 'profile-2';
		$profile_2 = new Profile( $profileName_2 );

		$profileVars_2['2-only'] = getUniqueString( 'two' );
		$profileVars_2['2-up'] = getUniqueString( 'two' );

		$profile_2->setVars( $profileVars_2 );

		/* Define inclusion */
		$profile_1->addAction( new Action\Meta\IncludeAction( $profileName_2 ) );

		/* Add all profiles to repo */
		$repo->addProfile( $profile_1 );
		$repo->addProfile( $profile_2 );

		$this->assertTrue( $repo->hasProfile( $profileName_1 ) );
		$this->assertTrue( $repo->hasProfile( $profileName_2 ) );

		$expectedVars = [
			'1-only' => $profileVars_1['1-only'],

			'2-only' => $profileVars_2['2-only'],
			'2-up'   => $profileVars_1['2-up'],
		];

		$actualVars = $repo->getProfileVars( $profileName_1 );

		ksort( $expectedVars );
		ksort( $actualVars );

		$this->assertEquals( $expectedVars, $actualVars );
	}

	public function test_hasProfile()
	{
		$repo = $this->createObject();

		$profileName = getUniqueString( 'profile-' );

		$this->assertFalse( $repo->hasProfile( $profileName ) );
	}
}
