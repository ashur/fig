<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use \Huxtable\CLI;
use \Huxtable\Core\File;

/**
 * @param	array	$apps
 * @return	Huxtable\CLI\Output
 */
function listApps( array $apps )
{
	$output = new CLI\Output;

	if( count( $apps ) == 0 )
	{
		$output->line( "fig: No apps found. See 'fig add'." );
	}

	$formattedString = new CLI\Format\String;

	if( !empty( $apps ) )
	{
		$padTop = false;
		foreach( $apps as $app )
		{
			if( $padTop)
			{
				$output->line();
			}

			/* App Name */
			$formattedString->foregroundColor( 'green' );
			$formattedString->setString( $app->getName() . '/' );
			$output->line( $formattedString );

			/* Profiles */
			$formattedString->foregroundColor( 'cyan' );
			$profiles = $app->getProfiles();

			foreach( $profiles as $profile )
			{
				$profileName = $profile->getName();
				$profileSummary = "  {$profileName}";

				/* Extending profiles */
				$extendsProfile = $profile->getParentName();
				if( !is_null( $extendsProfile ) )
				{
					$stringProfile = new CLI\Format\String;
					$stringProfile->foregroundColor( 'purple' );
					$stringProfile->setString( $profileName );

					$profileSummary = "  {$stringProfile} -> {$extendsProfile}";
				}

				$output->line( $profileSummary );
			}

			$padTop = true;
		}
	}

	return $output;
}

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
