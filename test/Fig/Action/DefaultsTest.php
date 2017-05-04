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
	public function testInvalidAction( $action )
	{
		$properties['name'] = time();

		$properties['defaults']['action'] = $action;
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';

		$defaults = new Fig\Action\Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidDomain( $domain )
	{
		$properties['name'] = time();

		$properties['defaults']['action'] = 'read';
		$properties['defaults']['domain'] = $domain;
		$properties['defaults']['key'] = 'foo';

		$defaults = new Fig\Action\Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidKey( $key )
	{
		$properties['name'] = time();

		$properties['defaults']['action'] = 'read';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = $key;

		$defaults = new Fig\Action\Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidValue( $value )
	{
		$properties['name'] = time();

		$properties['defaults']['action'] = 'write';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';
		$properties['defaults']['value'] = $value;

		$defaults = new Fig\Action\Defaults( $properties );
	}
}
