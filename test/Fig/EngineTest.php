<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use FigTest\TestCase;

class EngineTest extends TestCase
{
	/* Providers */

	public function provider_varNames() : array
	{
		return [
			['{{time}}'],
			['{{ time }}'],
			['{{  time  }}'],
			['{{time  }}'],
			['{{	time}}'],
		];
	}


	/* Tests */

	public function test_renderTemplate_replacesUndefinedVariables()
	{
		$time = time();

		$basePattern = 'name-%s-%s';
		$template = sprintf( $basePattern, '{{ time }}', '{{ undefined_variable }}' );
		$expectedString = sprintf( $basePattern, $time, '' );

		$vars = [ 'time' => $time ];

		$actualString = Engine::renderTemplate( $template, $vars );

		$this->assertEquals( $expectedString, $actualString );
	}

	/**
	 * @dataProvider	provider_varNames
	 */
	public function test_renderTemplate_supportsVaryingWhitespace( string $varName )
	{
		$time = time();

		$basePattern = 'name-%s';
		$template = sprintf( $basePattern, $varName );
		$expectedString = sprintf( $basePattern, $time );

		$vars = [ 'time' => $time ];

		$actualString = Engine::renderTemplate( $template, $vars );

		$this->assertEquals( $expectedString, $actualString );
	}
}
