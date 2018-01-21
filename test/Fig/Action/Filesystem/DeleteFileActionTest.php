<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Cranberry\Filesystem as CranberryFilesystem;
use Fig\Action;
use Fig\Action\AbstractAction;
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
	public function createObject() : AbstractFileAction
	{
		$name = getUniqueString( 'my action ' );
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$action = new DeleteFileAction( $name, $targetPath );

		return $action;
	}

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );
		$action = new DeleteFileAction( $name, $targetPath );

		return $action;
	}

	/**
	 * Creates and returns an instance of DeleteFileAction using the given target path
	 *
	 * @param	string	$targetPath
	 *
	 * @return	Fig\Action\Filesystem\DeleteFileAction
	 */
	public function createObject_fromTargetPath( string $targetPath ) : AbstractFileAction
	{
		$name = getUniqueString( 'my action ' );
		$action = new DeleteFileAction( $name, $targetPath );

		return $action;
	}


	/* Providers */

	public function provider_ActionObject() : array
	{
		$action = $this->createObject();

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
		$action = $this->createObject_fromTargetPath( $targetPath );

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
		$action = $this->createObject();

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
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_ignoringErrors( AbstractAction $action )
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$nodeMock = $this->getNodeMock( CranberryFilesystem\File::class );

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

		$action->ignoreErrors( true );
		$result = $action->deploy( $filesystemMock );

		$this->assertFalse( $result->didError() );
		$this->assertEquals( $exceptionMessage, $result->getOutput() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_ignoringOutput( AbstractAction $action )
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$nodeMock = $this->getNodeMock( CranberryFilesystem\File::class );

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

		$action->ignoreOutput( true );
		$result = $action->deploy( $filesystemMock );

		$this->assertTrue( $result->didError() );

		/* Note: Because `DeleteFileAction` outputs 'OK' on success, this test
		   differs from other instances of `test_deploy_ignoringOutput` by
		   testing the error state instead. */
		$this->assertEquals( Action\Result::STRING_STATUS_ERROR, $result->getOutput() );
	}

	/**
	 * @dataProvider	provider_NodeClasses
	 */
	public function test_existingUndeletableNode_causesError( string $nodeClass )
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );
		$action = $this->createObject_fromTargetPath( $targetPath );

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

		$result = $action->deploy( $filesystemMock );

		$this->assertTrue( $result->didError() );
		$this->assertEquals( $exceptionMessage, $result->getOutput() );
	}

	public function test_nonExistentTargetPath_doesNotCauseError()
	{
		$action = $this->createObject();

		$figDirectoryMock = $this->getNodeMock( CranberryFilesystem\Directory::class );
		$filesystem = new Filesystem\Filesystem( $figDirectoryMock );

		$result = $action->deploy( $filesystem );

		$this->assertFalse( $result->didError() );
	}

	public function test_nonExistentTargetPath_outputsOK()
	{
		$action = $this->createObject();

		$figDirectoryMock = $this->getNodeMock( CranberryFilesystem\Directory::class );
		$filesystem = new Filesystem\Filesystem( $figDirectoryMock );

		$result = $action->deploy( $filesystem );

		$this->assertEquals( Action\Result::STRING_STATUS_SUCCESS, $result->getOutput() );
	}

	public function test_getSubtitle()
	{
		$action = new DeleteFileAction( 'My File Action', '~/Desktop/hello.txt' );
		$this->assertEquals( 'delete', $action->getSubtitle() );
	}
}
