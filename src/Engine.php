<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Filesystem;
use Fig\Exception;

class Engine
{
	/**
	 * Parses template string string for tokens
	 *
	 * Returns associative array of matches
	 *
	 * @param	string	$template	Template to be parsed — ex., 'hello, {{who}}'
	 *
	 * @return	array
	 */
	static public function getTokensFromTemplate( string $template ) : array
	{
		$results = [];

		$tokenPattern = sprintf( '/\{\{\s*(%s+)\s*\}\}/', '[a-zA-Z0-9_\-]' );
		preg_match_all( $tokenPattern, $template, $matches );

		$tokenCount = count( $matches[0] );

		/* Build an associative array of matches ["{{ var_1 }}" => "var_1", ...] */
		for( $t = 0; $t < $tokenCount; $t++ )
		{
			$key = $matches[1][$t];
			$token = $matches[0][$t];

			$results[$token] = $key;
		}

		return $results;
	}

	/**
	 * Renders template string, replacing var tokens with their values
	 *
	 * @param	string	$template	Template to be rendered — ex., 'hello, {{who}}'
	 *
	 * @param	array	$vars		Array of keys with scalar values — ex., ['who' => 'world']
	 *
	 * @throws	Fig\Exception\ProfileSyntaxException	If var value references itself
	 *
	 * @return	string
	 */
	static public function renderTemplate( string $template, array $vars ) : string
	{
		$string = $template;

		$templateTokenMatches = self::getTokensFromTemplate( $template );
		$tokenCount = count( $templateTokenMatches );

		if( $tokenCount == 0 )
		{
			return $template;
		}

		/* Parse $vars values for tokens (ex., ["who" => "{{first}} {{last}}"]) */
		$maxRounds = ceil( log( count( $vars ), 2 ) );

		for( $round = 0; $round < $maxRounds; $round++ )
		{
			foreach( $vars as $varName => $varValue )
			{
				$varTokenMatches = self::getTokensFromTemplate( $varValue );

				if( in_array( $varName, $varTokenMatches ) )
				{
					$exceptionMessage = sprintf( 'Recursive definition of variable \'%s\': "%s"', $varName, $varValue );
					throw new Exception\ProfileSyntaxException( $exceptionMessage, Exception\ProfileSyntaxException::RECURSION );
				}

				if( count( $varTokenMatches ) > 0 )
				{
					$vars[$varName] = self::replaceTokensInTemplate( $varValue, $varTokenMatches, $vars );
				}
			}
		}

		$string = self::replaceTokensInTemplate( $string, $templateTokenMatches, $vars );

		return $string;
	}

	/**
	 * Replaces instances of tokens with value of corresponding var
	 *
	 * @param	string	$template	Template string containing tokens to be replaced — ex., 'hello, {{who}}'
	 *
	 * @param	array	$tokens	Associative array of tokens and var names — ex., ['{{who}}' => 'who']
	 *
	 * @param	array	$vars	Array of keys with scalar values — ex., ['who' => 'world']
	 *
	 * @return	string
	 */
	static public function replaceTokensInTemplate( string $template, array $tokens, array $vars ) : string
	{
		foreach( $tokens as $token => $varName )
		{
			if( isset( $vars[$varName] ) )
			{
				$template = str_replace( $token, $vars[$varName], $template );
			}
			else
			{
				/* var is undefined */
			}
		}

		return $template;
	}
}
