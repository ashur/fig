<?php

/*
 * This file is part of Fig
 */

use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
	/**
	 * @return	array
	 */
	public function invalidPropertyDefinitionProvider()
	{
		return [
			[ 'name', true, 'self::isStringish', null ],
			[ 'boolean', false, 'self::isBooleanish', 1234 ],
			[ 'null', true, 'is_null', 'hello' ],
		];
	}

	/**
	 * @return	array
	 */
	public function optionalPropertyProvider()
	{
		return [
			[false],
			[function()
			{
				return false;
			}]
		];
	}

	/**
	 * @return	array
	 */
	public function requiredPropertyProvider()
	{
		return [
			[true],
			[function()
			{
				return true;
			}]
		];
	}

	/**
	 * @return	array
	 */
	public function validPropertyDefinitionProvider()
	{
		return [
			[ 'name', true, 'self::isStringish', 'hello' ],
			[ 'boolean', false, 'self::isBooleanish', false ],
			[ 'null', true, 'is_null', null ],
		];
	}

	/**
	 * @dataProvider		invalidPropertyDefinitionProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testDefinePropertyWithInvalidValue( $name, $required, $validator, $value )
	{
		$model = new Fig\Model();
		$model->defineProperty( $name, $required, $validator );

		$properties = [ $name => $value ];
		$model->setPropertyValues( $properties );
	}

	/**
	 * @dataProvider	validPropertyDefinitionProvider
	 */
	public function testDefinePropertyWithValidValue( $name, $required, $validator, $value )
	{
		$model = new Fig\Model();
		$model->defineProperty( $name, $required, $validator );

		$properties = [ $name => $value ];
		$model->setPropertyValues( $properties );

		$this->assertEquals( $value, $model->$name );
	}

	/**
	 *
	 */
	public function testDefinePropertyWithCustomSetter()
	{
		$model = new Fig\Model();
		$model->defineProperty( 'name', false, 'self::isStringish', function( $value )
		{
			$this->name = strtoupper( $value );
		});

		$properties = [ 'name' => 'fig' ];
		$model->setPropertyValues( $properties );

		$this->assertEquals( 'FIG', $model->name );
	}

	/**
	 * @dataProvider		optionalPropertyProvider
	 */
	public function testDefineOptionalProperty( $required )
	{
		$model = new Fig\Model();
		$model->defineProperty( 'name', $required, 'self::isStringish' );

		$properties = [ 'foo' => 'bar' ];
		$model->setPropertyValues( $properties );
	}

	/**
	 * @dataProvider		requiredPropertyProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testDefineRequiredProperty( $required )
	{
		$model = new Fig\Model();
		$model->defineProperty( 'name', $required, 'self::isStringish' );

		$properties = [ 'foo' => 'bar' ];
		$model->setPropertyValues( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingRequiredPropertyThrowsException()
	{
		$model = new Fig\Model();

		$model->defineProperty( 'name', true, 'self::isStringish' );
		$model->defineProperty( 'optional', false, 'self::isStringish' );

		$properties = ['optional' => 'hello'];
		$model->setPropertyValues( $properties );
	}
}
