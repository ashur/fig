<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Format;
use Cranberry\CLI\Output;

/**
 * @param	array	$apps
 * @return	Huxtable\CLI\Output
 */
function listApps( array $apps )
{
	$output = new Output\Output;

	if( count( $apps ) == 0 )
	{
		$output->line( "fig: No apps found. See 'fig add'." );
	}

	$formattedString = new Format\String;

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
					$stringProfile = new Format\String;
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
