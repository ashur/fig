<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Filesystem;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
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

	static public function getTempDirectory() : Filesystem\Directory
	{
		$tempPathname = sprintf( '%s/tmp-%s', dirname( __DIR__ ), str_replace( '\\', '_', __CLASS__ ) );
		return new Filesystem\Directory( $tempPathname );
	}

	static public function setUpBeforeClass()
	{
		$tempDirectory = self::getTempDirectory();

		if( !$tempDirectory->exists() )
		{
			$tempDirectory->create();
		}
	}

	public function test_getFilesystemNodeFromPath_withDirectory_returnsDirectory()
	{
		$tempDirectory = self::getTempDirectory();
		$targetDirectory = $tempDirectory->getChild( getUniqueString( 'dir-' ), Filesystem\Node::DIRECTORY );
		$targetDirectory->create();

		$targetPath = $targetDirectory->getPathname();

		$this->assertTrue( file_exists( $targetPath ) );

		$engine = $this->getEngineObject();
		$targetNode = $engine->getFilesystemNodeFromPath( $targetPath );

		$this->assertEquals( Filesystem\Directory::class, get_class( $targetNode ) );
	}

	public function test_getFilesystemNodeFromPath_withFile_returnsFile()
	{
		$tempDirectory = self::getTempDirectory();
		$targetFile = $tempDirectory->getChild( getUniqueString( 'file-' ), Filesystem\Node::FILE );
		$targetFile->create();

		$targetPath = $targetFile->getPathname();

		$this->assertTrue( file_exists( $targetPath ) );

		$engine = $this->getEngineObject();
		$targetNode = $engine->getFilesystemNodeFromPath( $targetPath );

		$this->assertEquals( Filesystem\File::class, get_class( $targetNode ) );
	}

	public function test_getFilesystemNodeFromPath_withLink_returnsLink()
	{
		$tempDirectory = self::getTempDirectory();
		$file = $tempDirectory->getChild( getUniqueString( 'file-' ), Filesystem\Node::FILE );
		$file->create();

		$linkFilename = getUniqueString( 'link-' );
		$link = $tempDirectory->getChild( $linkFilename, Filesystem\Node::LINK );
		symlink( $file, $link );

		$this->assertTrue( file_exists( $file->getPathname() ) );
		$this->assertTrue( is_link( $link->getPathname() ) );

		$engine = $this->getEngineObject();
		$linkNode = $engine->getFilesystemNodeFromPath( $link->getPathname() );

		$this->assertEquals( Filesystem\Link::class, get_class( $linkNode ) );
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::FILESYSTEM_NODE_NOT_FOUND
	 */
	public function test_getFilesystemNodeFromPath_withNonExistentPath_throwsExceptionIfTypeNotSpecified()
	{
		$pathname = getUniqueString( self::getTempDirectory() . '/' );

		$engine = $this->getEngineObject();

		$this->assertFalse( file_exists( $pathname ) );

		$node = $engine->getFilesystemNodeFromPath( $pathname );
	}

	public function provider_nodeType_expectedClass() : array
	{
		return [
			[Filesystem\Node::DIRECTORY, Filesystem\Directory::class],
			[Filesystem\Node::FILE, Filesystem\File::class],
			[Filesystem\Node::LINK, Filesystem\Link::class],
		];
	}

	/**
	* @dataProvider	provider_nodeType_expectedClass
	*/
	public function test_getFilesystemNodeFromPath_withNonExistentPathAndSpecifiedType_returnsNodeObject( int $nodeType, string $expectedClass )
	{
		$pathname = getUniqueString( self::getTempDirectory() . '/' );

		$engine = $this->getEngineObject();

		$this->assertFalse( file_exists( $pathname ) );

		$node = $engine->getFilesystemNodeFromPath( $pathname, $nodeType );

		$this->assertEquals( $expectedClass, get_class( $node ) );
	}

	public function test_getProfileAssetNode_returnsNode()
	{
		$profileName = getUniqueString( 'profile-' );
		$assetName = getUniqueString( 'asset-' );

		/*
		 * Asset file; i.e., `~/.fig/<repo>/assets>/<profile>/<file>`
		 */
		$profileAssetFileMock = $this
 			->getMockBuilder( Filesystem\Directory::class )
 			->disableOriginalConstructor()
 			->getMock();

		/*
		 * Assets directory for profile; i.e., `~/.fig/<repo>/assets/<profile>`
		 */
		$profileAssetsDirectoryMock = $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getChild'] )
			->getMock();

		/* Mock behavior of Cranberry\Filesystem\Directory::getChild when
		   attempting to get non-existent child node */
		$profileAssetsDirectoryMock
			->method( 'getChild' )
			->willReturn( $profileAssetFileMock );

		$profileAssetsDirectoryMock
			->expects( $this->once() )
			->method( 'getChild' )
			->with( $assetName );

		/*
		 * Top-level assets directory; i.e., `~/.fig/<repo>/assets`
		 */
		$figDirectoryMock = $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getChild'] )
			->getMock();

		$figDirectoryMock
			->method( 'getChild' )
			->willReturn( $profileAssetsDirectoryMock );

		$figDirectoryMock
			->expects( $this->once() )
			->method( 'getChild' )
			->with( $profileName );

		$engine = new Engine( $figDirectoryMock );

		$profileAssetNode = $engine->getProfileAssetNode( $profileName, $assetName );

		$this->assertEquals( $profileAssetFileMock, $profileAssetNode );
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::FILESYSTEM_NODE_NOT_FOUND
	 */
	public function test_getProfileAssetNode_throwsExceptionForNonExistentAsset()
	{
		$profileName = getUniqueString( 'profile-' );
		$assetName = getUniqueString( 'asset-' );

		/*
		 * Assets directory for profile; i.e., `~/.fig/<repo>/assets/<profile>`
		 */
		$profileAssetsDirectoryMock = $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getChild'] )
			->getMock();

		/* Mock behavior of Cranberry\Filesystem\Directory::getChild when
		   attempting to get non-existent child node */
		$profileAssetsDirectoryMock
			->method( 'getChild' )
			->will( $this->throwException( new \BadMethodCallException ) );

		$profileAssetsDirectoryMock
			->expects( $this->once() )
			->method( 'getChild' )
			->with( $assetName );

		/*
		 * Top-level assets directory; i.e., `~/.fig/<repo>/assets`
		 */
		$figDirectoryMock = $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->setMethods( ['getChild'] )
			->getMock();

		$figDirectoryMock
			->method( 'getChild' )
			->willReturn( $profileAssetsDirectoryMock );

		$figDirectoryMock
			->expects( $this->once() )
			->method( 'getChild' )
			->with( $profileName );

		$engine = new Engine( $figDirectoryMock );

		$engine->getProfileAssetNode( $profileName, $assetName );
	}

	static public function tearDownAfterClass()
	{
		$tempDirectory = self::getTempDirectory();

		if( $tempDirectory->exists() )
		{
			$tempDirectory->delete();
		}
	}
}
