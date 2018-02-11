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

		$outputMock = $this->createOutputMock();

		$application = new Application( $filesystem, $shell, $outputMock );
		return $application;
	}

	public function createObject_withOutput( Output $output )
	{
		$figDirectory = getTemporaryDirectory()
			->getChild( '.fig', \Cranberry\Filesystem\Node::DIRECTORY );
		$filesystem = new Filesystem\Filesystem( $figDirectory );

		$shell = new Shell\Shell();

		$application = new Application( $filesystem, $shell, $output );
		return $application;
	}

	public function createOutputMock() : Output
	{
		$mock = $this->getMockBuilder( Output::class )
			->disableOriginalConstructor()
			->setMethods( ['writeActionHeader','writeActionResult'] )
			->getMock();

		return $mock;
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
		/* File */
		$tempDirectory = getTemporaryDirectory();

		$filename = getUniqueString( 'file-' );
		$file = $tempDirectory->getChild( $filename, \Cranberry\Filesystem\Node::FILE );
		$file->create();

		$this->assertTrue( $file->exists() );

		/* Action */
		$action = new Action\Filesystem\DeleteFileAction( 'delete temp file', $file->getPathname() );

		/* Expectations */
		$expectedResult = new Action\Result( Action\Result::STRING_STATUS_SUCCESS, false );

		$outputMock = $this->createOutputMock();
		$outputMock
			->expects( $this->once() )
			->method( 'writeActionHeader' )
			->with(
				$action->getType(),
				$action->getSubtitle(),
				$action->getName()
			);
		$outputMock
			->expects( $this->once() )
			->method( 'writeActionResult' )
			->with( $expectedResult );

		/* Deploy */
		$application = $this->createObject_withOutput( $outputMock );
		$application->deployActions( [$action], [] );

		$this->assertFalse( $file->exists() );
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
		/* Action */
		$action = new Action\Shell\CommandAction( 'example', 'echo', ['{{greeting}}, {{who}}.'] );

		$greeting = getUniqueString( 'hello-' );
		$who = getUniqueString( 'world-' );

		$vars = ['greeting' => $greeting, 'who' => $who];

		/* Expectations */
		$expectedResult = new Action\Result( "{$greeting}, {$who}.", false );

		$outputMock = $this->createOutputMock();
		$outputMock
			->expects( $this->once() )
			->method( 'writeActionHeader' )
			->with(
				$action->getType(),
				$action->getSubtitle(),
				$action->getName()
			);
		$outputMock
			->expects( $this->once() )
			->method( 'writeActionResult' )
			->with( $expectedResult );

		/* Deploy */
		$application = $this->createObject_withOutput( $outputMock );
		$application->deployActions( [$action], $vars );
	}

	public function test_deployProfile()
	{
		/* Profile */
		$profileName = getUniqueString( 'profile-' );
		$profile = new Profile( $profileName );

		/* Actions: Command */
		$commandAction = new Action\Shell\CommandAction( 'example', 'echo', ['{{greeting}}, {{who}}.'] );
		$profile->addAction( $commandAction );

		/* Actions: Delete File */
		$tempDirectory = getTemporaryDirectory();

		$filename = getUniqueString( 'file-' );
		$file = $tempDirectory->getChild( $filename, \Cranberry\Filesystem\Node::FILE );
		$file->create();

		$this->assertTrue( $file->exists() );

		$filesystemAction = new Action\Filesystem\DeleteFileAction( 'delete temp file', $file->getPathname() );
		$profile->addAction( $filesystemAction );

		/* Vars */
		$greeting = getUniqueString( 'hello-' );
		$who = getUniqueString( 'world-' );

		$vars = ['greeting' => $greeting, 'who' => $who];
		$profile->setVars( $vars );

		/* Repository */
		$repoName = getUniqueString( 'repo-' );
		$repository = new Repository( $repoName );

		$repository->addProfile( $profile );

		/* Expectations */
		$expectedCommandResult = new Action\Result( "{$greeting}, {$who}.", false );
		$expectedFilesystemResult = new Action\Result( Action\Result::STRING_STATUS_SUCCESS, false );

		$outputMock = $this->createOutputMock();
		$outputMock
			->expects( $this->exactly(2) )
			->method( 'writeActionHeader' )
			->withConsecutive(
				[ $commandAction->getType(), $commandAction->getSubtitle(), $commandAction->getName() ],
				[ $filesystemAction->getType(), $filesystemAction->getSubtitle(), $filesystemAction->getName() ]
			);
		$outputMock
			->expects( $this->atLeastOnce() )
			->method( 'writeActionResult' )
			->withConsecutive(
				[$expectedCommandResult],
				[$expectedFilesystemResult]
			);

		/* Deployment */
		$application = $this->createObject_withOutput( $outputMock );
		$application->addRepository( $repository );

		$application->deployProfile( $repoName, $profileName );

		/* Tests */
		$this->assertFalse( $file->exists() );
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
