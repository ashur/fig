<?php

/*
 * This file is part of Fig
 */
namespace Fig;

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

	for( $i = 0; $i < count( $labels ); $i++ )
	{
		if( empty( $queryPieces[$i] ) )
		{
			break;
		}

		$params[$labels[$i]] = $queryPieces[$i];
	}

	return $params;
}
