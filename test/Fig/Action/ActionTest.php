<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use PHPUnit\Framework\TestCase;

class ActionTest extends TestCase
{
	/**
	 * A collection of values users might use to mean `true`
	 *
	 * @return	array
	 */
	public function affirmativeValuesProvider()
	{
		return [
			[ true ],
			[ 'true' ],
			[ 'True' ],
			[ 'TRUE' ],
			[ 'yes' ],
			[ 'Yes' ],
			[ 'YES' ],
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
			[ false ],
			[ true ],
		];
	}

	/**
	 * A collection of values users might use to mean `false`
	 *
	 * @return	array
	 */
	public function negativeValuesProvider()
	{
		return [
			[ false ],
			[ 'false' ],
			[ 'False' ],
			[ 'FALSE' ],
			[ 'no' ],
			[ 'No' ],
			[ 'NO' ],
		];
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingName()
	{
		$properties['defaults']['action'] = 'read';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';

		$action = new Defaults( $properties );
	}

	/**
	 * @dataProvider	affirmativeValuesProvider
	 */
	public function testIgnoreErrorsAffirmative( $ignoreErrors )
	{
		$properties['name'] = 'foo-' . time();
		$properties['command'] = 'echo hello';
		$properties['ignore_errors'] = $ignoreErrors;

		$action = new Command( $properties );

		$this->assertTrue( $action->ignoreErrors );
	}

	/**
	 * @dataProvider	negativeValuesProvider
	 */
	public function testIgnoreErrorsNegative( $ignoreErrors )
	{
		$properties['name'] = 'foo-' . time();
		$properties['command'] = 'echo hello';
		$properties['ignore_errors'] = $ignoreErrors;

		$action = new Command( $properties );

		$this->assertFalse( $action->ignoreErrors );
	}

	/**
	 * @dataProvider	affirmativeValuesProvider
	 */
	public function testIgnoreOutputAffirmative( $ignoreOutput )
	{
		$properties['name'] = 'foo-' . time();
		$properties['command'] = 'echo hello';
		$properties['ignore_output'] = $ignoreOutput;

		$action = new Command( $properties );

		$this->assertTrue( $action->ignoreOutput );
	}

	/**
	 * @dataProvider	negativeValuesProvider
	 */
	public function testIgnoreOutputNegative( $ignoreOutput )
	{
		$properties['name'] = 'foo-' . time();
		$properties['command'] = 'echo hello';
		$properties['ignore_output'] = $ignoreOutput;

		$action = new Command( $properties );

		$this->assertFalse( $action->ignoreOutput );
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

		$defaults = new Defaults( $properties );
	}

	/**
	 * @dataProvider		validStringProvider
	 */
	public function testValidName( $name )
	{
		$properties['name'] = $name;
		$properties['command'] = 'echo hello';

		$action = new Command( $properties );

		$this->assertEquals( $name, $action->name );
	}

	/**
	 * String-ish values
	 *
	 * @return	array
	 */
	public function validStringProvider()
	{
		return [
			[ 'hello' ],
			[ time() ],
		];
	}
}
