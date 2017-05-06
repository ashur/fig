<?php

/*
 * This file is part of Fig
 */

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
	/**
	 * Invalid values for `file` value
	 *
	 * @return	array
	 */
	public function invalidFileValueProvider()
	{
		return [
			[ 'string' ],
			[ false ],
			[ 1234 ],
			[ (object)[] ],
		];
	}

	/**
	 * Non-string values
	 *
	 * @return	array
	 */
	public function invalidStringProvider()
	{
		return [
			[ [] ],
			[ (object)[] ],
			[ true ],
			[ false ],
		];
	}

	/**
	 * Invalid file contents values
	 *
	 * @return	array
	 */
	public function invalidContentsProvider()
	{
		return [
			[ (object)[] ],
		];
	}

	/**
	 * @dataProvider		invalidFileValueProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidFileDefinition( $file )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file'] = $file;

		$action = new Fig\Action\File( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingFile()
	{
		$properties['name'] = 'foo-' . time();
		$action = new Fig\Action\File( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidAction( $action )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = $action;
		$properties['file']['path'] = '~/Desktop';

		$file = new Fig\Action\File( $properties );
	}

	/**
	 * @dataProvider		invalidContentsProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidContents( $contents )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'create';
		$properties['file']['path'] = '~/Desktop';
		$properties['file']['contents'] = $contents;

		$file = new Fig\Action\File( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidPath( $path )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'create';
		$properties['file']['path'] = $path;

		$file = new Fig\Action\File( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidSource( $source )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'replace';
		$properties['file']['path'] = '~/Desktop';
		$properties['file']['source'] = $source;

		$file = new Fig\Action\File( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingPath()
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'create';

		$file = new Fig\Action\File( $properties );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function testUnsupportedAction()
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'foo';
		$properties['file']['path'] = '~/Desktop';

		$file = new Fig\Action\File( $properties );
	}
}
