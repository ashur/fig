<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Cranberry\Filesystem;
use Fig\Action\BaseAction;
use Fig\Engine;
use PHPUnit\Framework\TestCase;

class DeleteFileActionTest extends TestCase
{
	public function getEngineMock( Filesystem\Node $node ) : Engine
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['getFilesystemNodeFromPath'] )
			->getMock();

		$engineMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $node );

		return $engineMock;
	}

	public function getNodeMock( string $nodeClass ) : Filesystem\Node
	{
		$nodeMock = $this
			->getMockBuilder( $nodeClass )
			->disableOriginalConstructor()
			->setMethods( ['delete','exists'] )
			->getMock();

		return $nodeMock;
	}

	public function provider_nodeClasses() : array
	{
		return [
			[ Filesystem\File::class ],
			[ Filesystem\Directory::class ],
		];
	}

	/**
	 * @dataProvider	provider_nodeClasses
	 */
	public function test_deploy_callsEngineGetFilesystemNodeFromPath( string $nodeClass )
	{
		$targetPath = '~/Desktop/' . microtime( true );
		$action = new DeleteFileAction( 'My File Action', $targetPath );

		$nodeMock = $this->getNodeMock( $nodeClass );

		$engineMock = $this->getEngineMock( $nodeMock );
		$engineMock
			->expects( $this->once() )
			->method( 'getFilesystemNodeFromPath' )
			->with( $targetPath );

		$action->deploy( $engineMock );
	}

	/**
	 * @dataProvider	provider_nodeClasses
	 */
	public function test_deploy_callsNodeDelete( string $nodeClass )
	{
		$targetPath = '~/Desktop/' . microtime( true );
		$action = new DeleteFileAction( 'My File Action', $targetPath );

		$nodeMock = $this->getNodeMock( $nodeClass );
		$nodeMock
			->expects( $this->once() )
			->method( 'delete' );

		$engineMock = $this->getEngineMock( $nodeMock );

		$action->deploy( $engineMock );
	}

	public function test_getSubtitle()
	{
		$action = new DeleteFileAction( 'My File Action', '~/Desktop/hello.txt' );
		$this->assertEquals( 'delete', $action->getSubtitle() );
	}

	public function test_nonExistentTargetPath_doesNotCauseError()
	{
		$targetPath = '~/Desktop/' . microtime( true );
		$action = new DeleteFileAction( 'My File Action', $targetPath );
		$engine = new Engine();

		$action->deploy( $engine );

		$this->assertFalse( $action->didError() );
	}

	public function test_nonExistentTargetPath_outputsOK()
	{
		$targetPath = '~/Desktop/' . microtime( true );
		$action = new DeleteFileAction( 'My File Action', $targetPath );
		$engine = new Engine();

		$action->deploy( $engine );

		$this->assertEquals( BaseAction::STRING_STATUS_SUCCESS, $action->getOutput() );
	}
}
