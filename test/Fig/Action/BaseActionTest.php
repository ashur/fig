<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use FigTest\Action\TestCase;

class BaseActionTest extends TestCase
{
	public function getStub() : BaseAction
	{
		$stub = $this->getMockForAbstractClass( BaseAction::class );
		return $stub;
	}

	/* Consumed by FigTest\Action\TestCase::test_getType */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$action = new ExampleAction( $actionName );

		return [
			[$action]
		];
	}

	public function provider_didError() : array
	{
		return [
			[true],
			[false],
		];
	}

	/**
	 * @dataProvider	provider_didError
	 */
	public function test_didError_alwaysReturnsFalse_whenIgnoringErrors( $didError )
	{
		$exampleAction = new ExampleAction( 'name' );

		$exampleAction->ignoreErrors( true );
		$exampleAction->___setDidError( $didError );

		$this->assertEquals( false, $exampleAction->didError() );
	}

	public function test_didError_returnsNullBeforeDeployment()
	{
		$exampleAction = new ExampleAction( 'name' );
		$this->assertEquals( null, $exampleAction->didError() );
	}

	/**
	 * @dataProvider	provider_didError
	 */
	public function test_didError_returnsValueByDefault( $didError )
	{
		$exampleAction = new ExampleAction( 'name' );

		$exampleAction->___setDidError( $didError );

		$this->assertEquals( $didError, $exampleAction->didError() );
	}

	public function test_getName()
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

	public function test_getOutput_returnsNullBeforeDeployment()
	{
		$exampleAction = new ExampleAction( 'name' );
		$this->assertEquals( null, $exampleAction->getOutput() );
	}

	public function test_getOutput_returnsStringOK_whenIgnoringOutput()
	{
		$exampleAction = new ExampleAction( 'name' );

		$exampleAction->ignoreOutput( true );

		$outputString = (string) microtime( true );
		$exampleAction->___setOutputString( $outputString );

		$this->assertEquals( BaseAction::STRING_STATUS_SUCCESS, $exampleAction->getOutput() );
	}

	public function test_getOutput_returnsValueByDefault()
	{
		$exampleAction = new ExampleAction( 'name' );

		$outputString = (string) microtime( true );
		$exampleAction->___setOutputString( $outputString );

		$this->assertEquals( $outputString, $exampleAction->getOutput() );
	}

	public function test_getProfileName_returnsNull_whenUndefined()
	{
		$action = new ExampleAction( 'name' );

		$this->assertNull( $action->getProfileName() );
	}

	public function test_getProfileName_returnsString()
	{
		$action = new ExampleAction( 'name' );

		$profileName = sprintf( 'profile-', microtime( true ) );
		$action->setProfileName( $profileName );

		$this->assertEquals( $profileName, $action->getProfileName() );
	}

	public function test_getSubtitle()
	{
		$exampleAction = new ExampleAction( 'name' );

		$this->assertEquals( 'subtitle', $exampleAction->getSubtitle() );
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

	public function test_isDeprecated_returnsFalseByDefault()
	{
		$exampleAction = new ExampleAction( 'name' );

		$this->assertFalse( $exampleAction->isDeprecated() );
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
	 * @var	string
	 */
	protected $type = 'Example';

	/**
	 * @param	string	$name
	 *
	 * @return	void
	 */
	public function __construct( string $name )
	{
		$this->name = $name;
	}

	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine ){}

	/**
	 * Returns action subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return 'subtitle';
	}

	/**
	 * Sets didError to simulate deployment
	 *
	 * @param	boolean	$didError
	 *
	 * @return	void
	 */
	public function ___setDidError( bool $didError )
	{
		$this->didError = $didError;
	}

	/**
	 * Sets output string to simulate deployment
	 *
	 * @param	string	$outputString
	 *
	 * @return	void
	 */
	public function ___setOutputString( string $outputString )
	{
		$this->outputString = $outputString;
	}
}
