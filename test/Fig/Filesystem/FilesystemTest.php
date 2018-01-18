<?php

/*
 * This file is part of Fig
 */
namespace Fig\Filesystem;

use Cranberry\Filesystem as CranberryFilesystem;
use FigTest\TestCase;

class FilesystemTest extends TestCase
{
	/*
	 * Helpers
	 */
	public function getFigDirectoryMock() : CranberryFilesystem\Directory
	{
		return $this
			->getMockBuilder( CranberryFilesystem\Directory::class )
			->disableOriginalConstructor()
			->getMock();
	}

	static public function getTempDirectory() : CranberryFilesystem\Directory
	{
		$tempPathname = sprintf( '%s/tmp-%s', dirname( dirname( __DIR__ ) ), str_replace( '\\', '_', __CLASS__ ) );
		return new CranberryFilesystem\Directory( $tempPathname );
	}

	/*
	 * Providers
	 */
	public function provider_nodeType_expectedClass() : array
 	{
 		return [
 			[CranberryFilesystem\Node::DIRECTORY, CranberryFilesystem\Directory::class],
 			[CranberryFilesystem\Node::FILE, CranberryFilesystem\File::class],
 			[CranberryFilesystem\Node::LINK, CranberryFilesystem\Link::class],
 		];
 	}

	/*
	 * Tests
	 */
	public function test_getFilesystemNodeFromPath_withDirectory_returnsDirectory()
 	{
 		$tempDirectory = self::getTempDirectory();
 		$targetDirectory = $tempDirectory->getChild( getUniqueString( 'dir-' ), CranberryFilesystem\Node::DIRECTORY );
 		$targetDirectory->create();

 		$targetPath = $targetDirectory->getPathname();

 		$this->assertTrue( file_exists( $targetPath ) );

		$figDirectoryMock = $this->getFigDirectoryMock();
		$filesystem = new Filesystem( $figDirectoryMock );

 		$targetNode = $filesystem->getFilesystemNodeFromPath( $targetPath );

 		$this->assertEquals( CranberryFilesystem\Directory::class, get_class( $targetNode ) );
 	}

	public function test_getFilesystemNodeFromPath_withFile_returnsFile()
	{
		$tempDirectory = self::getTempDirectory();
		$targetFile = $tempDirectory->getChild( getUniqueString( 'file-' ), CranberryFilesystem\Node::FILE );
		$targetFile->create();

		$targetPath = $targetFile->getPathname();

		$this->assertTrue( file_exists( $targetPath ) );

		$figDirectoryMock = $this->getFigDirectoryMock();
		$filesystem = new Filesystem( $figDirectoryMock );

		$targetNode = $filesystem->getFilesystemNodeFromPath( $targetPath );

		$this->assertEquals( CranberryFilesystem\File::class, get_class( $targetNode ) );
	}

	public function test_getFilesystemNodeFromPath_withLink_returnsLink()
	{
		$tempDirectory = self::getTempDirectory();
		$file = $tempDirectory->getChild( getUniqueString( 'file-' ), CranberryFilesystem\Node::FILE );
		$file->create();

		$linkFilename = getUniqueString( 'link-' );
		$link = $tempDirectory->getChild( $linkFilename, CranberryFilesystem\Node::LINK );
		symlink( $file, $link );

		$this->assertTrue( file_exists( $file->getPathname() ) );
		$this->assertTrue( is_link( $link->getPathname() ) );

		$figDirectoryMock = $this->getFigDirectoryMock();
		$filesystem = new Filesystem( $figDirectoryMock );

		$linkNode = $filesystem->getFilesystemNodeFromPath( $link->getPathname() );

		$this->assertEquals( CranberryFilesystem\Link::class, get_class( $linkNode ) );
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::FILESYSTEM_NODE_NOT_FOUND
	 */
	public function test_getFilesystemNodeFromPath_withNonExistentPath_throwsExceptionIfTypeNotSpecified()
	{
		$pathname = getUniqueString( self::getTempDirectory() . '/' );

		$figDirectoryMock = $this->getFigDirectoryMock();
		$filesystem = new Filesystem( $figDirectoryMock );

		$this->assertFalse( file_exists( $pathname ) );

		$node = $filesystem->getFilesystemNodeFromPath( $pathname );
	}

	/**
 	* @dataProvider	provider_nodeType_expectedClass
 	*/
 	public function test_getFilesystemNodeFromPath_withNonExistentPathAndSpecifiedType_returnsNodeObject( int $nodeType, string $expectedClass )
 	{
 		$pathname = getUniqueString( self::getTempDirectory() . '/' );

		$figDirectoryMock = $this->getFigDirectoryMock();
		$filesystem = new Filesystem( $figDirectoryMock );

 		$this->assertFalse( file_exists( $pathname ) );

 		$node = $filesystem->getFilesystemNodeFromPath( $pathname, $nodeType );

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
 			->getMockBuilder( CranberryFilesystem\Directory::class )
 			->disableOriginalConstructor()
 			->getMock();

		/*
		 * Assets directory for profile; i.e., `~/.fig/<repo>/assets/<profile>`
		 */
		$profileAssetsDirectoryMock = $this
			->getMockBuilder( CranberryFilesystem\Directory::class )
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
			->getMockBuilder( CranberryFilesystem\Directory::class )
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

		$filesystem = new Filesystem( $figDirectoryMock );

		$profileAssetNode = $filesystem->getProfileAssetNode( $profileName, $assetName );

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
			->getMockBuilder( CranberryFilesystem\Directory::class )
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
			->getMockBuilder( CranberryFilesystem\Directory::class )
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

		$filesystem = new Filesystem( $figDirectoryMock );
		$filesystem->getProfileAssetNode( $profileName, $assetName );
	}

	/*
	 * setUp & tearDown
	 */
	static public function setUpBeforeClass()
	{
		$tempDirectory = self::getTempDirectory();

		if( !$tempDirectory->exists() )
		{
			$tempDirectory->create();
		}
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