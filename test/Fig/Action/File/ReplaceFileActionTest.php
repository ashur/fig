<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Cranberry\Filesystem;
use Fig\Engine;
use Fig\Exception;
use PHPUnit\Framework\TestCase;

class ReplaceFileActionTest extends TestCase
{
	public function getEngineMock( Filesystem\Node $node ) : Engine
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath'] )
			->getMock();

		$engineMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $node );

		return $engineMock;
	}

	public function getEngineObject() : Engine
	{
		$figDirectoryMock = $this->getFigDirectoryMock();
		$engine = new Engine( $figDirectoryMock );

		return $engine;
	}

	public function getFigDirectoryMock() : Filesystem\Directory
	{
		return $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->getMock();
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

	/* Consumed by FigTest\Action\TestCase::test_getType */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$action = new ReplaceFileAction( $actionName, 'hello.txt', '~/Desktop/hello.txt' );

		return [
			[$action]
		];
	}

	public function provider_nodeClasses() : array
	{
		return [
			[ Filesystem\File::class ],
			[ Filesystem\Directory::class ],
			[ Filesystem\Link::class ],
		];
	}

	public function test_deploy_calls_assetNodeCopyTo()
	{
		$targetPathname = sprintf( '/usr/local/foo/%s', microtime( true ) );

		$targetNodeParentMock = $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( true );

		$targetNodeMock = $this
			->getMockBuilder( Filesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['delete','getParent','getPathname'] )
			->getMock();
		$targetNodeMock
			->method( 'delete' )
			->willReturn( true );
		$targetNodeMock
			->method( 'getPathname' )
			->willReturn( $targetPathname );
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$assetNodeMock = $this
			->getMockBuilder( Filesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['copyTo'] )
			->getMock();
		$assetNodeMock
			->expects( $this->once() )
			->method( 'copyTo' )
			->with( $targetNodeMock );

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$engineMock
			->method( 'getProfileAssetNode' )
			->willReturn( $assetNodeMock );
		$engineMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = new ReplaceFileAction( 'My File Action', 'hello.txt', $targetPathname );
		$action->setProfileName( 'profile_name' );

		$action->deploy( $engineMock );

		$this->assertFalse( $action->didError() );
		$this->assertEquals( \Fig\Action\BaseAction::STRING_STATUS_SUCCESS, $action->getOutput() );
	}

	public function provider_deploy_getsFilesystemNodeOfSameTypeAsAssetNode() : array
	{
		return [
			[new Filesystem\Directory( microtime( true ) ), Filesystem\Node::DIRECTORY],
			[new Filesystem\File( microtime( true ) ),      Filesystem\Node::FILE],
			[new Filesystem\Link( microtime( true ) ),      Filesystem\Node::LINK],
		];
	}

	/**
	 * @dataProvider	provider_deploy_getsFilesystemNodeOfSameTypeAsAssetNode
	 */
	public function test_deploy_getsFilesystemNodeOfSameTypeAsAssetNode( Filesystem\Node $assetNode, string $targetNodeClass )
	{
		$targetPathname = sprintf( '~/Desktop/%s', microtime( true ) );

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();

		$engineMock
			->method( 'getProfileAssetNode' )
			->willReturn( $assetNode );

		$engineMock
			->expects( $this->once() )
			->method( 'getFilesystemNodeFromPath' )
			->with(
				$targetPathname,
				$targetNodeClass
			);

		/* Throw an exception artificially to end `deploy` execution */
		$engineMock
			->method( 'getFilesystemNodeFromPath' )
			->will( $this->throwException( new \Exception( 'End of test', 1024 ) ) );

		$action = new ReplaceFileAction( 'My File Action', 'hello.txt', $targetPathname );
		$action->setProfileName( 'profile_name' );

		try
		{
			$action->deploy( $engineMock );
		}
		catch( \Exception $e )
		{
			/* Instead of mocking out the rest of a successful deployment,
			   just catch the fake exception and stop. */
		}
	}

	public function test_deploy_withNonexistentAsset_causesError()
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['getProfileAssetNode'] )
			->getMock();

		$engineMock
			->method( 'getProfileAssetNode' )
			->will( $this->throwException( new Exception\RuntimeException ) );

		$action = new ReplaceFileAction( 'My File Action', 'hello.txt', '~/Desktop/hello.txt' );

		$profileName = sprintf( 'profile-%s', microtime( true ) );
		$action->setProfileName( $profileName );

		$action->deploy( $engineMock );

		$this->assertTrue( $action->didError() );
	}

	/**
	 * @expectedException	LogicException
	 */
	public function test_deploy_withUndefinedProfileName_throwsException()
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods()
			->getMock();

		$action = new ReplaceFileAction( 'My File Action', 'hello.txt', '~/Desktop/hello.txt' );
		$action->deploy( $engineMock );
	}

	public function test_deploy_withUndeletableExistingTargetNode_causesError()
	{
		$targetPathname = sprintf( '/usr/local/foo/%s', microtime( true ) );

		$targetNodeParentMock = $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( true );

		$targetNodeMock = $this
			->getMockBuilder( Filesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['delete','getParent','getPathname'] )
			->getMock();
		$targetNodeMock
			->method( 'delete' )
			->will( $this->throwException( new Filesystem\Exception( Filesystem\Node::ERROR_STRING_DELETE, Filesystem\Node::ERROR_CODE_PERMISSIONS ) ) );
		$targetNodeMock
			->method( 'getPathname' )
			->willReturn( $targetPathname );
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$engineMock
			->method( 'getProfileAssetNode' )
			->willReturn( new Filesystem\File( microtime( true ) ) );
		$engineMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = new ReplaceFileAction( 'My File Action', 'hello.txt', $targetPathname );
		$action->setProfileName( 'profile_name' );

		$action->deploy( $engineMock );

		$this->assertTrue( $action->didError() );

		$expectedErrorMessage = sprintf( BaseFileAction::ERROR_STRING_UNDELETABLE_NODE, $targetPathname, BaseFileAction::ERROR_STRING_PERMISSION_DENIED );
		$this->assertEquals( $expectedErrorMessage, $action->getOutput() );
	}

	public function test_deploy_withUnwritableTargetParent_causesError()
	{
		$targetPathname = sprintf( '/usr/local/foo/%s', microtime( true ) );

		$targetNodeParentMock = $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getPathname','isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'getPathname' )
			->willReturn( dirname( $targetPathname ) );
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( false );
		$targetNodeParentMock
			->expects( $this->once() )
			->method( 'isWritable' );

		$targetNodeMock = $this
			->getMockBuilder( Filesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['getParent'] )
			->getMock();
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$engineMock
			->method( 'getProfileAssetNode' )
			->willReturn( new Filesystem\File( microtime( true ) ) );
		$engineMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = new ReplaceFileAction( 'My File Action', 'hello.txt', $targetPathname );
		$action->setProfileName( 'profile_name' );

		$action->deploy( $engineMock );

		$this->assertTrue( $action->didError() );

		$expectedErrorMessage = sprintf( BaseFileAction::ERROR_STRING_INVALIDTARGET, dirname( $targetPathname ), BaseFileAction::ERROR_STRING_PERMISSION_DENIED );
		$this->assertEquals( $expectedErrorMessage, $action->getOutput() );
	}

	public function test_getSourcePath_withVariableReplacement()
	{
		$filename = microtime( true );

		$pattern = '%s.txt';
		$sourcePath = sprintf( $pattern, '{{ filename }}' );
		$expectedPath = sprintf( $pattern, $filename );

		$action = new ReplaceFileAction( 'My Example Action', $sourcePath, '~/Desktop/hello.txt' );
		$action->setVariables( ['filename' => $filename ] );

		$this->assertEquals( $expectedPath, $action->getSourcePath() );
	}

	public function test_getSubtitle()
	{
		$action = new ReplaceFileAction( 'My File Action', 'hello.txt', '~/Desktop/hello.txt' );
		$this->assertEquals( 'replace', $action->getSubtitle() );
	}
}
