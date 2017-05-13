<?php

/*
 * This file is part of Fig
 */

use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
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
			[ false ],
			[ true ],
		];
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidIncludeDefinition( $include )
	{
		$properties['name'] = 'foo-' . time();
		$properties['include'] = $include;

		$action = new Fig\Action\Profile( $properties );
	}

	/**
	 * @dataProvider		validStringProvider
	 */
	public function testValidIncludeDefinition( $include )
	{
		$properties['name'] = 'foo-' . time();
		$properties['include'] = $include;

		$action = new Fig\Action\Profile( $properties );

		$this->assertEquals( $include, $action->getIncludedProfileName() );
	}

	/**
	 * Non-string values
	 *
	 * @return	array
	 */
	public function validStringProvider()
	{
		return [
			[ 'foo' ],
			[ 'foo-' . time() ],
			[ time() ],
		];
	}
}
