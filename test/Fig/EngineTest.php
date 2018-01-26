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

	public function test_getTokensFromTemplate()
	{
		$template = '{{ salutation}}, {{ addressee }}. ({{salutation }})';
		$expectedTokens = [
			'{{ salutation}}' => 'salutation',
			'{{ addressee }}' => 'addressee',
			'{{salutation }}' => 'salutation',
		];

		$actualTokens = Engine::getTokensFromTemplate( $template );

		$this->assertEquals( $expectedTokens, $actualTokens );
	}

	public function test_renderTemplate_supportsVarsComposedOfVars()
	{
		$vars = [
			'greeting' => '{{ salutation}}, {{ addressee }}.',
			'addressee' => 'world',
			'salutation' => 'Hello',
		];

		$template = 'And then I said, "{{ greeting }}"';
		$expectedString = 'And then I said, "Hello, world."';

		$renderedString = Engine::renderTemplate( $template, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}

	/**
	 * @dataProvider	provider_varNames
	 */
	public function test_renderTemplate_tokensSupportVariableWhitespace( string $varName )
	{
		$time = time();

		$basePattern = 'name-%s';
		$template = sprintf( $basePattern, $varName );
		$expectedString = sprintf( $basePattern, $time );

		$vars = [ 'time' => $time ];

		$renderedString = Engine::renderTemplate( $template, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}

	public function test_renderTemplate_usingTemplateWithoutVars_returnsOriginalString()
	{
		$vars = [
			'salutation' => 'Hello',
			'addressee' => 'world',
			'greeting' => '{{ salutation}}, {{ addressee }}.',
		];

		$originalString = getUniqueString( 'Hello, world ' );
		$renderedString = Engine::renderTemplate( $originalString, $vars );

		$this->assertEquals( $originalString, $renderedString );
	}

	public function test_renderTemplate_withCrossReferencingVars()
	{
		$vars = [
			'eight' => '({{four}} + {{four}})',
			'four' => '({{two}} + {{two}})',
			'two' => '({{one}} + {{one}})',
			'one' => '1',
		];

		$template = '{{ eight }} = 8';

		$expectedString = '(((1 + 1) + (1 + 1)) + ((1 + 1) + (1 + 1))) = 8';
		$renderedString = Engine::renderTemplate( $template, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}

	/**
	 * @expectedException	Fig\Exception\ProfileSyntaxException
	 * @expectedExceptionCode	Fig\Exception\ProfileSyntaxException::RECURSION
	 */
	public function test_renderTemplate_withSelfReferencingVar_throwsException()
	{
		$vars = [
			'salutation' => 'howdy',
			'greeting' => '{{ greeting }}, neighbor.',
		];

		$template = 'And then I said, "{{ greeting }}"';
		$renderedString = Engine::renderTemplate( $template, $vars );
	}

	public function test_replaceTokensInTemplate()
	{
		$template = '{{ salutation}}, {{ addressee }}.';
		$tokens = [
			'{{ salutation}}' => 'salutation',
			'{{ addressee }}' => 'addressee',
		];
		$vars = [
			'addressee' => 'neighbor',
			'salutation' => 'howdy',
		];

		$expectedString = 'howdy, neighbor.';
		$renderedString = Engine::replaceTokensInTemplate( $template, $tokens, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}
}
