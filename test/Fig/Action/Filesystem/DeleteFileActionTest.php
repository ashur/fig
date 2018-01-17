<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Cranberry\Filesystem as CranberryFilesystem;
use Fig\Filesystem;
use FigTest\Action\Filesystem\TestCase;

class DeleteFileActionTest extends TestCase
{
	/* Helpers */

	/**
	 * Creates and returns an instance of DeleteFileAction
	 *
	 * @return	Fig\Action\Filesystem\DeleteFileAction
	 */
	public function createActionObject() : AbstractFileAction
	{
		$actionName = getUniqueString( 'my action ' );
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$action = new DeleteFileAction( $actionName, $targetPath );

		return $action;
	}

	/**
	 * Creates and returns an instance of DeleteFileAction using the given action name
	 *
	 * @param	string	$actionName
	 *
	 * @return	Fig\Action\Filesystem\DeleteFileAction
	 */
	public function createActionObject_fromActionName( string $actionName ) : AbstractFileAction
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );
		$action = new DeleteFileAction( $actionName, $targetPath );

		return $action;
	}

	/**
	 * Creates and returns an instance of DeleteFileAction using the given target path
	 *
	 * @param	string	$targetPath
	 *
	 * @return	Fig\Action\Filesystem\DeleteFileAction
	 */
	public function createActionObject_fromTargetPath( string $targetPath ) : AbstractFileAction
	{
		$actionName = getUniqueString( 'my action ' );
		$action = new DeleteFileAction( $actionName, $targetPath );

		return $action;
	}


	/* Providers */

	public function provider_ActionObject() : array
	{
		$action = $this->createActionObject();

		return [
			[$action]
		];
	}


	/* Tests */

	/**
	 * @dataProvider	provider_NodeClasses
	 */
	public function test_deploy_calls_getFilesystemNodeFromPath( string $nodeClass )
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );
		$action = $this->createActionObject_fromTargetPath( $targetPath );

		$nodeMock = $this->getNodeMock( $nodeClass );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath'] )
			->getMock();
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $nodeMock );
		$filesystemMock
			->expects( $this->once() )
			->method( 'getFilesystemNodeFromPath' )
			->with( $targetPath );

		$action->deploy( $filesystemMock );
	}

	/**
	 * @dataProvider	provider_NodeClasses
	 */
	public function test_deploy_calls_NodeDelete( string $nodeClass )
	{
		$action = $this->createActionObject();

		$nodeMock = $this->getNodeMock( $nodeClass );
		$nodeMock
			->expects( $this->once() )
			->method( 'delete' );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath'] )
			->getMock();
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $nodeMock );

		$action->deploy( $filesystemMock );
	}

	/**
	 * @dataProvider	provider_NodeClasses
	 */
	public function test_existingUndeletableNode_causesError( string $nodeClass )
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );
		$action = $this->createActionObject_fromTargetPath( $targetPath );

		$nodeMock = $this->getNodeMock( $nodeClass );

		/* Simulate exception thrown when attempting to delete undeletable
		   Cranberry\Filesystem\Node objects */
		$exceptionMessage = sprintf( CranberryFilesystem\Node::ERROR_STRING_DELETE, $targetPath, 'Permission denied' );
		$nodeMock
			->method( 'delete' )
			->will( $this->throwException( new CranberryFilesystem\Exception( $exceptionMessage ) ) );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath'] )
			->getMock();
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $nodeMock );

		$action->deploy( $filesystemMock );

		$this->assertTrue( $action->didError() );
		$this->assertEquals( $exceptionMessage, $action->getOutput() );
	}

	public function test_nonExistentTargetPath_doesNotCauseError()
	{
		$action = $this->createActionObject();

		$figDirectoryMock = $this->getNodeMock( CranberryFilesystem\Directory::class );
		$filesystem = new Filesystem\Filesystem( $figDirectoryMock );

		$action->deploy( $filesystem );

		$this->assertFalse( $action->didError() );
	}

	public function test_nonExistentTargetPath_outputsOK()
	{
		$action = $this->createActionObject();

		$figDirectoryMock = $this->getNodeMock( CranberryFilesystem\Directory::class );
		$filesystem = new Filesystem\Filesystem( $figDirectoryMock );

		$action->deploy( $filesystem );

		$this->assertEquals( DeleteFileAction::STRING_STATUS_SUCCESS, $action->getOutput() );
	}

	public function test_getSubtitle()
	{
		$action = new DeleteFileAction( 'My File Action', '~/Desktop/hello.txt' );
		$this->assertEquals( 'delete', $action->getSubtitle() );
	}
}
