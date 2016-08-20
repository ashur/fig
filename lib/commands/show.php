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
				$stringProfile = new CLI\Format\String;
				$stringProfile->setString( $profile->getName() );

				$profileSummary = "  {$stringProfile}";

				/* Extending profiles */
				$extendsProfile = $profile->getParentName();
				if( !is_null( $extendsProfile ) )
				{
					$stringBase = new CLI\Format\String;
					$stringBase->foregroundColor( 'purple' );
					$stringBase->setString( "» {$extendsProfile}" );

					$profileSummary = "  {$stringProfile} {$stringBase}";
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
 * @desc		List, create or delete apps
 * @usage		app
 */
$commandShow = new CLI\Command( 'show', 'List existing apps', function()
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