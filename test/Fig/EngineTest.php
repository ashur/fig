<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Filesystem;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
	static public function getTempDirectory() : Filesystem\Directory
	{
		return new Filesystem\Directory( dirname( __DIR__ ) . '/tmp' );
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
		$engine = new Engine();
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

		$engine = new Engine();
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

		$engine = new Engine();
		$targetNode = $engine->getFilesystemNodeFromPath( $targetPath );

		$this->assertEquals( Filesystem\File::class, get_class( $targetNode ) );
	}

	/**
	 * @expectedException	Fig\NonExistentFilesystemPathException
	 */
	public function test_getFilesystemNodeFromPath_withNonExistentPath_throwsException()
	{
		$targetPath = '~/Desktop/' . microtime( true );
		$engine = new Engine();

		$this->assertFalse( file_exists( $targetPath ) );

		$targetNode = $engine->getFilesystemNodeFromPath( $targetPath );
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
