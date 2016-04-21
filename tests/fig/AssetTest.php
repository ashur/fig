<?php

/*
 * This file is part of the Fig test suite
 */

use Huxtable\Core\File;

class AssetTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->dirTemp = new File\Directory( __DIR__ . "/fixtures/temp" );
		$this->dirTemp->create();
	}

	public function testCreation()
	{
		$dirTarget = new File\Directory( '/var/bar' );
		$asset = new Fig\Asset( $dirTarget );

		$this->assertEquals( $dirTarget, $asset->getTarget() );
	}

	public function testSetAction()
	{
		$dirSource = new File\Directory( '/var/foo' );
		$dirTarget = new File\Directory( '/var/bar' );

		$asset = new Fig\Asset( $dirTarget );

		// SKIP
		$asset->skip();
		$this->assertEquals( Fig\Asset::SKIP, $asset->getAction() );

		// CREATE
		$asset->create();
		$this->assertEquals( Fig\Asset::CREATE, $asset->getAction() );

		// REPLACE
		$asset->replaceWith( $dirSource );
		$this->assertEquals( Fig\Asset::REPLACE, $asset->getAction() );
		$this->assertEquals( $dirSource, $asset->getSource() );

		// DELETE
		$asset->delete();
		$this->assertEquals( Fig\Asset::DELETE, $asset->getAction() );
	}

	public function testEncodeSkip()
	{
		$dirTarget = new File\Directory( '/var/bar' );

		$assetExpected = new Fig\Asset( $dirTarget );
		$assetExpected->skip();

		$jsonEncoded = json_encode( $assetExpected );
		$jsonDecoded = json_decode( $jsonEncoded, true );

		$this->assertEquals( 'skip', $jsonDecoded['action'] );

		// Test instantiation from JSON
		$assetActual = Fig\Asset::getInstanceFromJSON( $jsonEncoded );

		$this->assertEquals(
			$assetExpected->getTarget()->getPathname(),
			$assetActual->getTarget()->getPathname()
		);

		$this->assertEquals(
			$assetExpected->getAction(),
			$assetActual->getAction()
		);
	}

	public function testEncodeCreate()
	{
		$dirTarget = new File\Directory( '/var/bar' );

		$assetExpected = new Fig\Asset( $dirTarget );
		$assetExpected->create();

		$jsonEncoded = json_encode( $assetExpected );
		$jsonDecoded = json_decode( $jsonEncoded, true );

		$this->assertEquals( 'create', $jsonDecoded['action'] );

		// Test instantiation from JSON
		$assetActual = Fig\Asset::getInstanceFromJSON( $jsonEncoded );

		$this->assertEquals(
			$assetExpected->getTarget()->getPathname(),
			$assetActual->getTarget()->getPathname()
		);

		$this->assertEquals(
			$assetExpected->getAction(),
			$assetActual->getAction()
		);
	}

	public function testEncodeReplace()
	{
		$pathSource = '/var/foo/src.php';
		$pathTarget = '/var/bar/dest.php';

		$fileSource = new File\File( $pathSource );
		$fileTarget = new File\File( $pathTarget );

		$assetExpected = new Fig\Asset( $fileTarget );
		$assetExpected->replaceWith( $fileSource );

		$jsonEncoded = json_encode( $assetExpected );
		$jsonDecoded = json_decode( $jsonEncoded, true );

		$this->assertEquals( 'replace', $jsonDecoded['action'] );

		// Test instantiation from JSON
		$assetActual = Fig\Asset::getInstanceFromJSON( $jsonEncoded );

		$this->assertEquals(
			$assetExpected->getTarget()->getPathname(),
			$assetActual->getTarget()->getPathname()
		);

		$this->assertEquals(
			$assetExpected->getSource()->getPathname(),
			$assetActual->getSource()->getPathname()
		);

		$this->assertEquals(
			$assetExpected->getAction(),
			$assetActual->getAction()
		);
	}

	public function testEncodeDelete()
	{
		$dirTarget = new File\Directory( '/var/bar' );

		$assetExpected = new Fig\Asset( $dirTarget );
		$assetExpected->delete();

		$jsonEncoded = json_encode( $assetExpected );
		$jsonDecoded = json_decode( $jsonEncoded, true );

		$this->assertEquals( 'delete', $jsonDecoded['action'] );

		// Test instantiation from JSON
		$assetActual = Fig\Asset::getInstanceFromJSON( $jsonEncoded );

		$this->assertEquals(
			$assetExpected->getTarget()->getPathname(),
			$assetActual->getTarget()->getPathname()
		);

		$this->assertEquals(
			$assetExpected->getAction(),
			$assetActual->getAction()
		);
	}

	/**
	 * @dataProvider	filesProvider
	 */
	public function testDeploySkip( File\file $fileTarget )
	{
		$asset = new Fig\Asset( $fileTarget );
		$asset->skip();

		if( rand( 0, 1 ) == 0 )
		{
			$fileTarget->create();
		}

		$fileExists = $fileTarget->exists();
		$asset->deploy();

		$this->assertEquals( $fileExists, $fileTarget->exists() );
	}

	/**
	 * @dataProvider	filesProvider
	 */
	public function testDeployCreate( File\file $fileTarget )
	{
		$asset = new Fig\Asset( $fileTarget );
		$asset->create();

		$this->assertFalse( $fileTarget->exists() );
		$asset->deploy();
		$this->assertTrue( $fileTarget->exists() );
	}

	/**
	 * @dataProvider	filesProvider
	 */
	public function testDeployReplaceFile()
	{
		$sourceFilename = rand( 0, 499 ) . '.php';
		$fileSource = $this->dirTemp->child( $sourceFilename );
		$fileSource->putContents( time() . PHP_EOL );

		$targetFilename = rand( 500, 999 ) . '.php';
		$fileTarget = $this->dirTemp->child( $targetFilename );

		$asset = new Fig\Asset( $fileTarget );
		$asset->replaceWith( $fileSource );

		$asset->deploy();
		$this->assertTrue( $fileTarget->exists() );
		$this->assertEquals( $fileSource->getContents(), $fileTarget->getContents() );
	}

	/**
	 * @dataProvider	filesProvider
	 */
	public function testDeployDelete( File\file $fileTarget )
	{
		$fileTarget->create();
		$this->assertTrue( $fileTarget->exists() );

		$asset = new Fig\Asset( $fileTarget );
		$asset->delete();

		$asset->deploy();
		$this->assertFalse( $fileTarget->exists() );
	}

	public function filesProvider()
	{
		$filename = rand( 0, 999 );
		$pathFileTest = __DIR__ . "/fixtures/temp/{$filename}";
		$file = rand( 0, 1 ) == 0 ? new File\File( $pathFileTest ) : new File\Directory( $pathFileTest );

		return [
			[$file]
		];
	}

	public function tearDown()
	{
		$this->dirTemp->delete();
	}
}
