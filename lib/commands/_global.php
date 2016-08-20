<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use \Huxtable\CLI;
use \Huxtable\Core\File;

/**
 * @param	string	$query			ex., "<app>/<profile>" or "<app>:<command>"
 * @param	string	$delimeter		ex., "/" or ":"
 * @param	array	$labels			ex., ['app','profile']
 * @return	array
 */
function parseQuery( $query, $delimiter, array $labels )
{
	$params = [];
	$queryPieces = explode( $delimiter, $query );

	$expectedPiecesCount = count( $labels );
	if( count( $queryPieces ) == $expectedPiecesCount )
	{
		for( $i = 0; $i < $expectedPiecesCount; $i++ )
		{
			if( empty( $queryPieces[$i] ) )
			{
				throw new CLI\Command\IncorrectUsageException();
			}

			$params[$labels[$i]] = $queryPieces[$i];
		}

		return $params;
	}

	throw new CLI\Command\IncorrectUsageException();
}

/*
 * Commands
 */
