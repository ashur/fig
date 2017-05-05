<?php

/*
 * This file is part of knockov
 */

use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
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
	 * @expectedException	BadMethodCallException
	 */
	public function testMissingName()
	{
		$properties['defaults']['action'] = 'read';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';

		$action = new Fig\Action\Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidName( $name )
	{
		$properties['name'] = $name;

		$properties['defaults']['action'] = 'read';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';

		$defaults = new Fig\Action\Defaults( $properties );
	}
}
