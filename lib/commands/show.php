<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI;
use Huxtable\CLI\Command;

/**
 * @param	array	$apps
 * @return	Huxtable\CLI\Output
 */
function listApps( array $apps )
{
	$output = new CLI\Output;

	if( count( $apps ) == 0 )
	{
		$output->line( "fig: No apps found. See 'fig create'." );
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
 * @command		app
 * @desc		List apps and their profiles
 * @usage		app
 */
$commandShow = new CLI\Command( 'show', 'List apps and their profiles', function()
{
	$fig = new Fig();

	try
	{
		$apps = $fig->getApps();
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}

	$output = listApps( $apps );
	return $output->flush();
});

$commandShow->registerAlias( 'ls' );

return $commandShow;
