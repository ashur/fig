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

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::PROFILE_NOT_FOUND
	 */
	public function test_getProfileActions_whichExtendsUndefinedProfile_throwsException()
	{
		$repo = $this->createObject();

		/* Construct profile which includes an undefined second profile */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		/* Action to include undefined profile */
		$undefinedProfileName = getUniqueString( 'undefined-profile-' );
		$extendAction = new Action\Meta\ExtendAction( $undefinedProfileName );

		$profile->addAction( $extendAction );

		$repo->addProfile( $profile );

		$this->assertTrue( $repo->hasProfile( $profileName ) );

		$repo->getProfileActions( $profileName );
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

	public function test_hasProfile()
	{
		$repo = $this->createObject();

		$profileName = getUniqueString( 'profile-' );

		$this->assertFalse( $repo->hasProfile( $profileName ) );
	}
}
