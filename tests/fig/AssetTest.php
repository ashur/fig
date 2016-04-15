<?php

/*
 * This file is part of the Fig test suite
 */

use Huxtable\Core\File;

class AssetTest extends PHPUnit_Framework_TestCase
{
	public function testCreation()
	{
		$fileSource = new File\File( '/var/foo/src.php' );
		$dirTarget = new File\Directory( '/var/bar' );

		$asset = new Fig\Asset( $fileSource, $dirTarget );

		$this->assertEquals( $fileSource, $asset->getSource() );
		$this->assertEquals( $dirTarget, $asset->getTarget() );
	}

	public function testEncode()
	{
		$pathSource = '/var/foo/src.php';
		$pathTarget = '/var/bar/dest.php';

		$fileSource = new File\File( $pathSource );
		$fileTarget = new File\File( $pathTarget );

		$asset = new Fig\Asset( $fileSource, $fileTarget );

		$jsonEncoded = json_encode( $asset );
		$jsonDecoded = json_decode( $jsonEncoded, true );

		$this->assertEquals( $pathSource, $jsonDecoded['source'] );
		$this->assertEquals( $pathTarget, $jsonDecoded['target'] );
	}
}
