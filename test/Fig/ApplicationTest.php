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
		$figDirectory = getTemporaryDirectory()
			->getChild( '.fig', \Cranberry\Filesystem\Node::DIRECTORY );

		$filesystem = new Filesystem\Filesystem( $figDirectory );
		$shell = new Shell\Shell();

		$application = new Application( $filesystem, $shell );
		return $application;
	}

	static public function tearDownAfterClass()
	{
		$tempDirectory = getTemporaryDirectory();

		if( $tempDirectory->exists() )
		{
			$tempDirectory->delete();
		}
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

	public function test_deployActions_withFilesystemAction()
	{
		$tempDirectory = getTemporaryDirectory();

		$filename = getUniqueString( 'file-' );
		$file = $tempDirectory->getChild( $filename, \Cranberry\Filesystem\Node::FILE );
		$file->create();

		$this->assertTrue( $file->exists() );

		$actions[] = new Action\Filesystem\DeleteFileAction( 'delete temp file', $file->getPathname() );

		$application = $this->createObject();
		$results = $application->deployActions( $actions, [] );

		$expectedResult = new Action\Result( Action\Result::STRING_STATUS_SUCCESS, false );

		$this->assertCount( 1, $results );
		$this->assertEquals( $expectedResult, $results[0] );
	}

	/**
	 * @expectedException	\LogicException
	 */
	public function test_deployActions_withNonDeployAbleAction_throwsException()
	{
		$actions[] = new Action\Meta\ExtendAction( 'another-profile' );

		$application = $this->createObject();

		$application->deployActions( $actions, [] );
	}

	public function test_deployActions_withShellAction()
	{
		$actions[] = new Action\Shell\CommandAction( 'example', 'echo', ['{{greeting}}, {{who}}.'] );

		$greeting = getUniqueString( 'hello-' );
		$who = getUniqueString( 'world-' );

		$vars = ['greeting' => $greeting, 'who' => $who];

		$application = $this->createObject();
		$results = $application->deployActions( $actions, $vars );

		$expectedResult = new Action\Result( "{$greeting}, {$who}.", false );

		$this->assertCount( 1, $results );
		$this->assertEquals( $expectedResult, $results[0] );
	}

	public function test_deployProfile()
	{
		/* Profile */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		/* Actions: Command */
		$profile->addAction( new Action\Shell\CommandAction( 'example', 'echo', ['{{greeting}}, {{who}}.'] ) );

		/* Actions: Delete File */
		$tempDirectory = getTemporaryDirectory();

		$filename = getUniqueString( 'file-' );
		$file = $tempDirectory->getChild( $filename, \Cranberry\Filesystem\Node::FILE );
		$file->create();

		$this->assertTrue( $file->exists() );

		$profile->addAction( new Action\Filesystem\DeleteFileAction( 'delete temp file', $file->getPathname() ) );

		/* Vars */
		$greeting = getUniqueString( 'hello-' );
		$who = getUniqueString( 'world-' );

		$vars = ['greeting' => $greeting, 'who' => $who];
		$profile->setVars( $vars );

		/* Repository */
		$repoName = getUniqueString( 'repo-' );
		$repository = new Repository( $repoName );

		$repository->addProfile( $profile );

		$application = $this->createObject();
		$application->addRepository( $repository );

		/* Deployment */
		$results = $application->deployProfile( $repoName, $profileName );

		/* Tests */
		$expectedResults[] = new Action\Result( "{$greeting}, {$who}.", false );
		$expectedResults[] = new Action\Result( Action\Result::STRING_STATUS_SUCCESS, false );

		$this->assertCount( 2, $results );
		$this->assertEquals( $expectedResults, $results );
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::REPOSITORY_NOT_FOUND
	 */
	public function test_deployProfile_withNonExistentRepository_throwsException()
	{
		$application = $this->createObject();

		$repoName = getUniqueString( 'repo-' );

		$application->deployProfile( $repoName, 'profile' );
	}

	public function test_hasRepository()
	{
		$application = $this->createObject();
		$repoName = getUniqueString( 'repo' );

		$this->assertFalse( $application->hasRepository( $repoName ) );
	}
}
