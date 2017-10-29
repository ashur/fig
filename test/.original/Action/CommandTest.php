<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

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
		$action = new Command( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidCommand( $command )
	{
		$properties['name'] = 'foo-' . time();
		$properties['command'] = $command;

		$action = new Command( $properties );
	}
}
