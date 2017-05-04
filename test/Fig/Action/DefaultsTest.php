<?php

/*
 * This file is part of knockov
 */

use PHPUnit\Framework\TestCase;

class DefaultsTest extends TestCase
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
		];
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidKey( $key )
	{
		$properties['name'] = time();

		$properties['defaults']['action'] = Fig\Action\Defaults::READ;
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = $key;

		$action = new Fig\Action\Defaults( $properties );
	}
}
