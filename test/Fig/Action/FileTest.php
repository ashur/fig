<?php

/*
 * This file is part of Fig
 */

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
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
