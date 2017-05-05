<?php

/*
 * This file is part of Fig
 */

use PHPUnit\Framework\TestCase;

class CommandTest extends TestCase
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
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingCommand()
	{
		$properties['name'] = 'foo-' . time();
		$action = new Fig\Action\Command( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidCommand( $command )
	{
		$properties['name'] = 'foo-' . time();
		$properties['command'] = $command;

		$action = new Fig\Action\Command( $properties );
	}
}
