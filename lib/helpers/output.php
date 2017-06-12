<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Format;
use Cranberry\CLI\Output;

/**
 * @param	array	$apps
 * @param	boolean		$listHiddenItems
 * @param	boolean		$longListing
 * @return	Cranberry\CLI\Output
 */
function listApps( array $apps, $listHiddenItems = false, $longListing = false )
{
	if( !$longListing )
	{
		$output = new Output\Listing;
	}
	else
	{
		$output = new Output\Output;
		$output->line( 'total ' . count( $apps ) );
	}

	if( count( $apps ) == 0 )
	{
		$output->line( "fig: No apps found. See 'fig add'." );
	}
	else
	{
		foreach( $apps as $app )
		{
			$stringApp = new Output\FormattedString;

			$appName = $app->getName();

			/* Hidden item */
			$firstChar = substr( $appName, 0, 1 );
			if( $firstChar == '.' )
			{
				if( !$listHiddenItems )
				{
					continue;
				}
			}

			$stringApp->foregroundColor( 'cyan' );
			$stringApp->setString( $appName );

			if( !$longListing )
			{
				$output->item( $stringApp );
			}
			else
			{
				$output->line( $stringApp );
			}
		}
	}

	return $output;
}

/**
 * @param	Fig\App		$app
 * @param	boolean		$listHiddenItems
 * @param	boolean		$longListing
 * @return	Cranberry\CLI\Output
 */
function listProfiles( App $app, $listHiddenItems = false, $longListing = false )
{
	/* Profiles */
	$profiles = $app->getProfiles();

	if( !$longListing )
	{
		$output = new Output\Listing;
	}
	else
	{
		$output = new Output\Output;
		$output->line( 'total ' . count( $profiles ) );
	}

	foreach( $profiles as $profile )
	{
		$stringProfile = new Output\FormattedString;

		$profileName = $profile->getName();
		$extendsProfile = $profile->getParentName();

		/* Hidden item */
		$firstChar = substr( $profileName, 0, 1 );
		if( $firstChar == '.' )
		{
			if( !$listHiddenItems )
			{
				continue;
			}
		}

		/* Color */
		$color = is_null( $extendsProfile ) ? 'gray' : 'purple';

		$stringProfile->foregroundColor( $color );
		$stringProfile->setString( $profileName );

		if( !$longListing )
		{
			$output->item( $stringProfile );
		}
		else
		{
			$profileSummary = $stringProfile;

			/* Extending profiles */
			if( !is_null( $extendsProfile ) )
			{
				$profileSummary = "{$stringProfile} -> {$extendsProfile}";
			}

			$output->line( $profileSummary );
		}
	}

	return $output;
}
