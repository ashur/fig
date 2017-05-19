<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
	/**
	 * Invalid $name values;
	 *
	 * @return	array
	 */
	public function invalidNameProvider()
	{
		return [
			[ [] ],
			[ (object)[] ],
			[ false ],
			[ true ],
			[ 'hello/world' ],
			[ 'hello:world' ],
			[ 'hello world' ],
		];
	}

	/**
	 * @dataProvider		invalidNameProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidName( $name )
	{
		$app = new App( $name );
	}

	/**
	 * @dataProvider		validNameProvider
	 */
	public function testValidName( $name )
	{
		$app = new App( $name );
		$this->assertEquals( $name, $app->getName() );
	}

	/**
	 * Valid $name values
	 *
	 * @return	array
	 */
	public function validNameProvider()
	{
		return [
			[ 'foo-' . time() ],
			[ 'föö-bar' ],
			[ '😋' ],
			[ time() ],
		];
	}
}
