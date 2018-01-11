<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use PHPUnit\Framework\TestCase;

class VariablizableTest extends TestCase
{
	public function getStub() : Variablizable
	{
		$stub = $this->getMockForAbstractClass( Variablizable::class );
		return $stub;
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function test_setVariables_withNonScalarValues_throwsException()
	{
		$stub = $this->getStub();
		$invalidVariables = [
			'scalar'	=> 'hello world',
			'nonscalar'	=> ['hello', 'world']
		];

		$stub->setVariables( $invalidVariables );
	}

	public function test_replaceVariablesInString_replacesUndefinedVariables()
	{
		$time = time();
		$variables = [ 'time' => $time ];

		$pattern = 'name-%s-%s';
		$nameString = sprintf( $pattern, '{{ time }}', '{{ undefined_variable }}' );
		$expectedName = sprintf( $pattern, $time, '' );

		$stub = $this->getStub();
		$stub->setVariables( $variables );

		$actualName = $stub->replaceVariablesInString( $nameString );
		$this->assertEquals( $expectedName, $actualName );
	}

	public function provider_replaceVariablesInString_supportsVariableWhitespace() : array
	{
		return [
			['{{time}}'],
			['{{ time }}'],
			['{{  time  }}'],
			['{{time  }}'],
			['{{ time}}'],
		];
	}

	/**
	 * @dataProvider	provider_replaceVariablesInString_supportsVariableWhitespace
	 */
	public function test_replaceVariablesInString_supportsVariableWhitespace( $variable )
	{
		$time = time();
		$variables = [ 'time' => $time ];

		$pattern = 'name-%s';
		$nameString = sprintf( $pattern, $variable );
		$expectedName = sprintf( $pattern, $time );

		$stub = $this->getStub();
		$stub->setVariables( $variables );

		$actualName = $stub->replaceVariablesInString( $nameString );

		$this->assertEquals( $expectedName, $actualName );
	}
}
