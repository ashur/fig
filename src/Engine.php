<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Filesystem;

class Engine
{
	/**
	 * description
	 *
	 * @param	string	$template	Template to be rendered — ex., 'hello, {{who}}'
	 *
	 * @param	array	$vars		Array of keys with scalar values — ex., ['who' => 'world']
	 *
	 * @param	int		$round		Current	round of recursion
	 *
	 * @throws	Fig\Exception\ProfileSyntaxException	If var value references itself
	 *
	 * @return	string
	 */
	static public function renderTemplate( string $template, array $vars, $round=0 ) : string
	{
		$string = $template;

		/* Parse $template for tokens (i.e., `{{ var_name }}`). */
		$tokenPattern = sprintf( '/\{\{\s*(%s+)\s*\}\}/', '[a-zA-Z0-9_\-]' );
		preg_match_all( $tokenPattern, $string, $matches );

		$tokenCount = count( $matches[0] );
		if( $tokenCount == 0 )
		{
			return $template;
		}

		/* Parse $vars values for var tokens. */
		$maxRounds = count( $vars ) - 1;
		if( $round <= $maxRounds )
		{
			$round++;

			$renderedVars = [];
			foreach( $vars as $key => $value )
			{
				$renderedVars[$key] = self::renderTemplate( $value, $vars, $round );
			}
		}
		else
		{
			$renderedVars = $vars;
		}

		/* Replace var tokens with values */
		for( $t = 0; $t < $tokenCount; $t++ )
		{
			$token = $matches[0][$t];
			$key   = $matches[1][$t];

			/* Replace $token in $template with matching $renderedVars value */
			if( isset( $vars[$key] ) )
			{
				$string = str_replace( $token, $renderedVars[$key], $string );
			}
			else
			{
				/* var is undefined */
			}
		}

		return $string;
	}
}
