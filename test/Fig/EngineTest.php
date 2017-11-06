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

	public function test_executeCommand_returnsArray()
	{
		$engine = $this->getEngineObject();

		$result = $engine->executeCommand( 'echo', ['hello'] );

		$this->assertTrue( is_array( $result ) );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertContains( 'hello', $result['output'] );

		$this->assertArrayHasKey( 'exitCode', $result );
		$this->assertEquals( 0, $result['exitCode'] );
	}

	public function test_getFilesystemNodeFromPath_withDirectory_returnsDirectory()
	{
		$tempDirectory = self::getTempDirectory();
		$targetDirectory = $tempDirectory->getChild( microtime( true ), Filesystem\Node::DIRECTORY );
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
		$targetFile = $tempDirectory->getChild( microtime( true ), Filesystem\Node::FILE );
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
		$file = $tempDirectory->getChild( microtime( true ), Filesystem\Node::FILE );
		$file->create();

		$linkFilename = 'link-' . microtime( true );
		$link = $tempDirectory->getChild( $linkFilename, Filesystem\Node::LINK );
		symlink( $file, $link );

		$this->assertTrue( file_exists( $file->getPathname() ) );
		$this->assertTrue( is_link( $link->getPathname() ) );

		$engine = $this->getEngineObject();
		$linkNode = $engine->getFilesystemNodeFromPath( $link->getPathname() );

		$this->assertEquals( Filesystem\Link::class, get_class( $linkNode ) );
	}

	/**
	 * @expectedException	Fig\NonExistentFilesystemPathException
	 */
	public function test_getFilesystemNodeFromPath_withNonExistentPath_throwsExceptionIfTypeNotSpecified()
	{
		$pathname = sprintf( '%s/%s', self::getTempDirectory(), microtime( true ) );

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
		$pathname = sprintf( '%s/%s', self::getTempDirectory(), microtime( true ) );

		$engine = $this->getEngineObject();

		$this->assertFalse( file_exists( $pathname ) );

		$node = $engine->getFilesystemNodeFromPath( $pathname, $nodeType );

		$this->assertEquals( $expectedClass, get_class( $node ) );
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
