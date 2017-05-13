<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use PHPUnit\Framework\TestCase;

class DefaultsTest extends TestCase
{
	/**
	 * Invalid values for `defaults` value
	 *
	 * @return	array
	 */
	public function invalidDefaultsValueProvider()
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
		];
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidAction( $action )
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['action'] = $action;
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';

		$defaults = new Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidDefaultsValueProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidDefaultsDefinition( $defaults )
	{
		$properties['name'] = 'foo-' . time();
		$properties['defaults'] = $defaults;

		$action = new Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidDomain( $domain )
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['action'] = 'read';
		$properties['defaults']['domain'] = $domain;
		$properties['defaults']['key'] = 'foo';

		$defaults = new Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidKey( $key )
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['action'] = 'read';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = $key;

		$defaults = new Defaults( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidValue( $value )
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['action'] = 'write';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';
		$properties['defaults']['value'] = $value;

		$defaults = new Defaults( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingAction()
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['domain'] = 'co.cabreramade.Fig';
		$properties['defaults']['key'] = 'foo';

		$defaults = new Defaults( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingDefaults()
	{
		$properties['name'] = 'foo-' . time();
		$action = new Defaults( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingDomain()
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['action'] = 'write';
		$properties['defaults']['key'] = 'foo';

		$defaults = new Defaults( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testSetInvalidAction()
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['action'] = 'foo-action';
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';

		$defaults = new Defaults( $properties );
	}

	/**
	 * @dataProvider	validActionProvider
	 */
	public function testSetValidAction( $actionName, $action )
	{
		$properties['name'] = 'foo-' . time();

		$properties['defaults']['action'] = $actionName;
		$properties['defaults']['domain'] = 'co.cabreramade.Fig';

		$defaults = new Defaults( $properties );

		$this->assertEquals( $action, $defaults->action );
		$this->assertEquals( $actionName, $defaults->actionName );
	}

	/**
	 * @return	array
	 */
	public function validActionProvider()
	{
		return [
			['read', Defaults::READ],
			['write', Defaults::WRITE],
			['delete', Defaults::DELETE],
		];
	}
}
