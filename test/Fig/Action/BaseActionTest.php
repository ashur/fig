<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use PHPUnit\Framework\TestCase;

class BaseActionTest extends TestCase
{
	public function getStub() : BaseAction
	{
		$stub = $this->getMockForAbstractClass( BaseAction::class );
		return $stub;
	}

	public function test_getName_returnsString()
	{
		$expectedName = 'action-' . microtime( true );
		$exampleAction = new ExampleAction( $expectedName );

		$actualName = $exampleAction->getName();
		$this->assertTrue( is_string( $actualName ) );
		$this->assertEquals( $expectedName, $actualName );
	}

	public function test_getName_supportsVariables()
	{
		$time = microtime( true );
		$name = 'action-{{ time }}';
		$expectedName = 'action-' . $time;

		$variables = ['time' => $time];

		$exampleAction = new ExampleAction( $name );
		$exampleAction->setVariables( $variables );

		$this->assertEquals( $expectedName, $exampleAction->getName() );
	}

	public function provider_ignoreMethods_supportBooleanishValues() : array
	{
		return [
			[true, true],
			['true', true],
			['True', true],
			['yes', true],
			['Yes', true],

			[false, false],
			['false', false],
			['False', false],
			['no', false],
			['No', false],
		];
	}

	/**
	 * @dataProvider	provider_ignoreMethods_supportBooleanishValues
	 */
	public function test_ignoreErrors_supportsBooleanishValues( $booleanish, $shouldIgnoreErrors )
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreErrors( $booleanish );

		$this->assertEquals( $shouldIgnoreErrors, $stubAction->willIgnoreErrors() );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function test_ignoreErrors_throwsException_givenNonBooleanishValue()
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreErrors( 'hello' );
	}

	/**
	 * @dataProvider	provider_ignoreMethods_supportBooleanishValues
	 */
	public function test_ignoreOutput_supportsBooleanishValues( $booleanish, $shouldIgnoreOutput )
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreOutput( $booleanish );

		$this->assertEquals( $shouldIgnoreOutput, $stubAction->willIgnoreOutput() );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function test_ignoreOutput_throwsException_givenNonBooleanishValue()
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreOutput( 'hello' );
	}

	public function test_willNotIgnoreErrorsByDefault()
	{
		$stubAction = $this->getStub();
		$this->assertFalse( $stubAction->willIgnoreErrors() );
	}

	public function test_willNotIgnoreOutputByDefault()
	{
		$stubAction = $this->getStub();
		$this->assertFalse( $stubAction->willIgnoreOutput() );
	}
}

class ExampleAction extends BaseAction
{
	/**
	 * @param	string	$name
	 *
	 * @return	void
	 */
	public function __construct( string $name )
	{
		$this->name = $name;
	}
}
