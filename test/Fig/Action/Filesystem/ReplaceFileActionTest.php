<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Filesystem;

use Cranberry\Filesystem as CranberryFilesystem;
use Fig\Action;
use Fig\Action\AbstractAction;
use Fig\Exception;
use Fig\Filesystem;
use FigTest\Action\Filesystem\TestCase;

class ReplaceFileActionTest extends TestCase
{
	/* Helpers */

	/**
	 * Creates and returns an instance of ReplaceFileAction
	 *
	 * @return	Fig\Action\Filesystem\ReplaceFileAction
	 */
	public function createObject() : AbstractFileAction
	{
		$actionName = getUniqueString( 'my action ' );
		$sourcePath = getUniqueString( 'file-' );
		$targetPath = getUniqueString( '/usr/local/foo/file-' );

		$action = new ReplaceFileAction( $actionName, $sourcePath, $targetPath );

		return $action;
	}

	/**
	 * Creates and returns an instance of ReplaceFileAction using the given action name
	 *
	 * @param	string	$actionName
	 *
	 * @return	Fig\Action\Filesystem\ReplaceFileAction
	 */
	public function createObject_fromName( string $name ) : AbstractAction
	{
		$sourcePath = getUniqueString( 'file-' );
		$targetPath = getUniqueString( '/usr/local/foo/file-' );

		$action = new ReplaceFileAction( $name, $sourcePath, $targetPath );

		return $action;
	}

	/**
	 * Creates and returns an instance of ReplaceFileAction using the given target path
	 *
	 * @param	string	$sourcePath
	 *
	 * @return	Fig\Action\Filesystem\ReplaceFileAction
	 */
	public function createObject_fromSourcePath( string $sourcePath ) : AbstractFileAction
	{
		$name = getUniqueString( 'my action ' );
		$targetPath = getUniqueString( '/usr/local/foo/file-' );

		$action = new ReplaceFileAction( $name, $sourcePath, $targetPath );

		return $action;
	}

	/**
	 * Creates and returns an instance of ReplaceFileAction using the given target path
	 *
	 * @param	string	$targetPath
	 *
	 * @return	Fig\Action\Filesystem\ReplaceFileAction
	 */
	public function createObject_fromTargetPath( string $targetPath ) : AbstractFileAction
	{
		$name = getUniqueString( 'my action ' );
		$sourcePath = getUniqueString( 'file-' );

		$action = new ReplaceFileAction( $name, $sourcePath, $targetPath );

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

	public function provider_deploy_getsFilesystemNodeOfSameTypeAsAssetNode() : array
	{
		return [
			[new CranberryFilesystem\Directory( getUniqueString( '/usr/local/foo/' ) ), CranberryFilesystem\Node::DIRECTORY],
			[new CranberryFilesystem\File( getUniqueString( '/usr/local/foo/' ) ),      CranberryFilesystem\Node::FILE],
			[new CranberryFilesystem\Link( getUniqueString( '/usr/local/foo/' ) ),      CranberryFilesystem\Node::LINK],
		];
	}


	/* Tests */

	public function test_deploy_calls_assetNodeCopyTo()
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$targetNodeParentMock = $this
			->getMockBuilder( CranberryFilesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( true );

		$targetNodeMock = $this
			->getMockBuilder( CranberryFilesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['delete','getParent','getPathname'] )
			->getMock();
		$targetNodeMock
			->method( 'delete' )
			->willReturn( true );
		$targetNodeMock
			->method( 'getPathname' )
			->willReturn( $targetPath );
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$assetNodeMock = $this
			->getMockBuilder( CranberryFilesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['copyTo'] )
			->getMock();
		$assetNodeMock
			->expects( $this->once() )
			->method( 'copyTo' )
			->with( $targetNodeMock );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$filesystemMock
			->method( 'getProfileAssetNode' )
			->willReturn( $assetNodeMock );
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = $this->createObject_fromTargetPath( $targetPath );
		$action->setProfileName( 'profile_name' );

		$result = $action->deploy( $filesystemMock );

		$this->assertFalse( $result->didError() );
		$this->assertEquals( Action\Result::STRING_STATUS_SUCCESS, $result->getOutput() );
	}

	/**
	 * @dataProvider	provider_deploy_getsFilesystemNodeOfSameTypeAsAssetNode
	 */
	public function test_deploy_getsFilesystemNodeOfSameTypeAsAssetNode( CranberryFilesystem\Node $assetNode, string $targetNodeClass )
	{
		$targetPath = getUniqueString( '/usr/local/foo/file-' );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();

		$filesystemMock
			->method( 'getProfileAssetNode' )
			->willReturn( $assetNode );

		$filesystemMock
			->expects( $this->once() )
			->method( 'getFilesystemNodeFromPath' )
			->with(
				$targetPath,
				$targetNodeClass
			);

		/* Throw an exception artificially to end `deploy` execution */
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->will( $this->throwException( new \Exception( 'End of test', 1024 ) ) );

		$action = $this->createObject_fromTargetPath( $targetPath );
		$action->setProfileName( 'profile_name' );

		try
		{
			$action->deploy( $filesystemMock );
		}
		catch( \Exception $e )
		{
			/* Instead of mocking out the rest of a successful deployment,
			   just catch the fake exception and stop. This is weird and
			   should be improved :( */
		}
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_ignoringErrors( AbstractAction $action )
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$targetNodeParentMock = $this
			->getMockBuilder( CranberryFilesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getPathname','isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'getPathname' )
			->willReturn( dirname( $targetPath ) );
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( false );
		$targetNodeParentMock
			->expects( $this->once() )
			->method( 'isWritable' );

		$targetNodeMock = $this
			->getMockBuilder( CranberryFilesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['getParent'] )
			->getMock();
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$filesystemMock
			->method( 'getProfileAssetNode' )
			->willReturn( new CranberryFilesystem\File( getUniqueString( 'file-' ) ) );
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = $this->createObject_fromTargetPath( $targetPath );
		$action->setProfileName( 'profile_name' );
		$action->ignoreErrors( true );

		$result = $action->deploy( $filesystemMock );

		$this->assertFalse( $result->didError() );

		$expectedErrorMessage = sprintf( ReplaceFileAction::ERROR_STRING_INVALIDTARGET, dirname( $targetPath ), ReplaceFileAction::ERROR_STRING_PERMISSION_DENIED );
		$this->assertEquals( $expectedErrorMessage, $result->getOutput() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_ignoringOutput( AbstractAction $action )
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$targetNodeParentMock = $this
			->getMockBuilder( CranberryFilesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getPathname','isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'getPathname' )
			->willReturn( dirname( $targetPath ) );
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( false );
		$targetNodeParentMock
			->expects( $this->once() )
			->method( 'isWritable' );

		$targetNodeMock = $this
			->getMockBuilder( CranberryFilesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['getParent'] )
			->getMock();
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$filesystemMock
			->method( 'getProfileAssetNode' )
			->willReturn( new CranberryFilesystem\File( getUniqueString( 'file-' ) ) );
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = $this->createObject_fromTargetPath( $targetPath );
		$action->setProfileName( 'profile_name' );
		$action->ignoreOutput( true );

		$result = $action->deploy( $filesystemMock );

		$this->assertTrue( $result->didError() );

		/* Note: Because `ReplaceFileAction` outputs 'OK' on success, this test
		   differs from other instances of `test_deploy_ignoringOutput` by
		   testing the error state instead. */
		$this->assertEquals( Action\Result::STRING_STATUS_ERROR, $result->getOutput() );
	}

	public function test_deploy_withNonexistentAsset_causesError()
	{
		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getProfileAssetNode'] )
			->getMock();
		$filesystemMock
			->method( 'getProfileAssetNode' )
			->will( $this->throwException( new Exception\RuntimeException ) );

		$profileName = getUniqueString( 'profile-' );

		$action = $this->createObject();
		$action->setProfileName( $profileName );

		$result = $action->deploy( $filesystemMock );

		$this->assertTrue( $result->didError() );
	}

	/**
	 * @expectedException	LogicException
	 */
	public function test_deploy_withUndefinedProfileName_throwsException()
	{
		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods()
			->getMock();

		$action = $this->createObject();
		$action->deploy( $filesystemMock );
	}

	public function test_deploy_withUndeletableExistingTargetNode_causesError()
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$targetNodeParentMock = $this
			->getMockBuilder( CranberryFilesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( true );

		$targetNodeMock = $this
			->getMockBuilder( CranberryFilesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['delete','getParent','getPathname'] )
			->getMock();
		$targetNodeMock
			->method( 'delete' )
			->will( $this->throwException( new CranberryFilesystem\Exception( CranberryFilesystem\Node::ERROR_STRING_DELETE, CranberryFilesystem\Node::ERROR_CODE_PERMISSIONS ) ) );
		$targetNodeMock
			->method( 'getPathname' )
			->willReturn( $targetPath );
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$filesystemMock
			->method( 'getProfileAssetNode' )
			->willReturn( new CranberryFilesystem\File( getUniqueString( 'file-' ) ) );
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = $this->createObject_fromTargetPath( $targetPath );
		$action->setProfileName( 'profile_name' );

		$result = $action->deploy( $filesystemMock );

		$this->assertTrue( $result->didError() );

		$expectedErrorMessage = sprintf( ReplaceFileAction::ERROR_STRING_UNDELETABLE_NODE, $targetPath, ReplaceFileAction::ERROR_STRING_PERMISSION_DENIED );
		$this->assertEquals( $expectedErrorMessage, $result->getOutput() );
	}

	public function test_deploy_withUnwritableTargetParent_causesError()
	{
		$targetPath = getUniqueString( '/usr/local/foo/' );

		$targetNodeParentMock = $this
			->getMockBuilder( CranberryFilesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getPathname','isWritable'] )
			->getMock();
		$targetNodeParentMock
			->method( 'getPathname' )
			->willReturn( dirname( $targetPath ) );
		$targetNodeParentMock
			->method( 'isWritable' )
			->willReturn( false );
		$targetNodeParentMock
			->expects( $this->once() )
			->method( 'isWritable' );

		$targetNodeMock = $this
			->getMockBuilder( CranberryFilesystem\File::class )
			->disableOriginalConstructor()
			->setMethods( ['getParent'] )
			->getMock();
		$targetNodeMock
			->method( 'getParent' )
			->willReturn( $targetNodeParentMock );

		$filesystemMock = $this
			->getMockBuilder( Filesystem\Filesystem::class )
			->disableOriginalConstructor()
			->setMethods( ['getFilesystemNodeFromPath','getProfileAssetNode'] )
			->getMock();
		$filesystemMock
			->method( 'getProfileAssetNode' )
			->willReturn( new CranberryFilesystem\File( getUniqueString( 'file-' ) ) );
		$filesystemMock
			->method( 'getFilesystemNodeFromPath' )
			->willReturn( $targetNodeMock );

		$action = $this->createObject_fromTargetPath( $targetPath );
		$action->setProfileName( 'profile_name' );

		$result = $action->deploy( $filesystemMock );

		$this->assertTrue( $result->didError() );

		$expectedErrorMessage = sprintf( ReplaceFileAction::ERROR_STRING_INVALIDTARGET, dirname( $targetPath ), ReplaceFileAction::ERROR_STRING_PERMISSION_DENIED );
		$this->assertEquals( $expectedErrorMessage, $result->getOutput() );
	}

	public function test_getSourcePath_withVariableReplacement()
	{
		$filename = getUniqueString( 'file-' );

		$pattern = '%s.txt';
		$sourcePath = sprintf( $pattern, '{{ filename }}' );
		$expectedPath = sprintf( $pattern, $filename );

		$action = $this->createObject_fromSourcePath( $sourcePath );
		$action->setVariables( ['filename' => $filename ] );

		$this->assertEquals( $expectedPath, $action->getSourcePath() );
	}

	public function test_getSubtitle()
	{
		$action = $this->createObject();
		$this->assertEquals( 'replace', $action->getSubtitle() );
	}
}
