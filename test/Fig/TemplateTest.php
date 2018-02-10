<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use FigTest\TestCase;

class TemplateTest extends TestCase
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

	public function test_getTokens()
	{
		$template = '{{ salutation}}, {{ addressee }}. ({{salutation }})';
		$expectedTokens = [
			'{{ salutation}}' => 'salutation',
			'{{ addressee }}' => 'addressee',
			'{{salutation }}' => 'salutation',
		];

		$actualTokens = Template::getTokens( $template );

		$this->assertEquals( $expectedTokens, $actualTokens );
	}

	public function test_render_supportsVarsComposedOfVars()
	{
		$vars = [
			'greeting' => '{{ salutation}}, {{ addressee }}.',
			'addressee' => 'world',
			'salutation' => 'Hello',
		];

		$template = 'And then I said, "{{ greeting }}"';
		$expectedString = 'And then I said, "Hello, world."';

		$renderedString = Template::render( $template, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}

	/**
	 * @dataProvider	provider_varNames
	 */
	public function test_render_tokensSupportVariableWhitespace( string $varName )
	{
		$time = time();

		$basePattern = 'name-%s';
		$template = sprintf( $basePattern, $varName );
		$expectedString = sprintf( $basePattern, $time );

		$vars = [ 'time' => $time ];

		$renderedString = Template::render( $template, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}

	public function test_render_usingTemplateWithoutVars_returnsOriginalString()
	{
		$vars = [
			'salutation' => 'Hello',
			'addressee' => 'world',
			'greeting' => '{{ salutation}}, {{ addressee }}.',
		];

		$originalString = getUniqueString( 'Hello, world ' );
		$renderedString = Template::render( $originalString, $vars );

		$this->assertEquals( $originalString, $renderedString );
	}

	public function test_render_withCrossReferencingVars()
	{
		$vars = [
			'eight' => '({{four}} + {{four}})',
			'four' => '({{two}} + {{two}})',
			'two' => '({{one}} + {{one}})',
			'one' => '1',
		];

		$template = '{{ eight }} = 8';

		$expectedString = '(((1 + 1) + (1 + 1)) + ((1 + 1) + (1 + 1))) = 8';
		$renderedString = Template::render( $template, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}

	/**
	 * @expectedException	Fig\Exception\ProfileSyntaxException
	 * @expectedExceptionCode	Fig\Exception\ProfileSyntaxException::RECURSION
	 */
	public function test_render_withSelfReferencingVar_throwsException()
	{
		$vars = [
			'salutation' => 'howdy',
			'greeting' => '{{ greeting }}, neighbor.',
		];

		$template = 'And then I said, "{{ greeting }}"';
		$renderedString = Template::render( $template, $vars );
	}

	public function test_replaceTokens()
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
		$renderedString = Template::replaceTokens( $template, $tokens, $vars );

		$this->assertEquals( $expectedString, $renderedString );
	}
}
