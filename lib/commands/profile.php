<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI;
use Huxtable\CLI\Input;

/**
 * @command		profile
 * @desc		Manage profiles
 * @usage		profile <subcommand>
 */
$commandProfile = new CLI\Command( 'profile', 'Manage profiles', function()
{
	// @todo	Figure out what to show/do here :flushed:
});

/**
 * @command		profile deploy
 * @desc		Deploy a profile (an alias for 'command run')
 * @usage		profile deploy <app>/<profile>
 */

// Defined in _global
$subcommandProfileDeploy = $commandDeploy;

/**
 * @command		profile update
 * @desc		Update profile assets from source
 * @usage		profile update [-y|--yes] <app>/<profile>
 * @options		-y, --yes
 */
$subcommandProfileUpdate = new CLI\Command( 'update', 'Update profile assets from source', function( $query )
{
	GLOBAL $fig;

	try
	{
		$params = parseQuery( $query, '/', ['app','profile'] );

		if( is_null( $this->getOptionValue( 'y' ) ) && is_null( $this->getOptionValue( 'yes' ) ) )
		{
			$continue = Input::prompt( "Are you sure you want to overwrite the {ul}{$query}{/ul} profile assets? (y/n)", true );
			if( strtolower( $continue ) != 'y' )
			{
				exit( 1 );
			}
		}

		$fig->updateProfileAssetsFromTarget( $params['app'], $params['profile'] );
	}
	catch( CLI\Command\IncorrectUsageException $e )
	{
		throw new CLI\Command\IncorrectUsageException( $this->getUsage(), 1 );
	}
	catch( \Exception $e )
	{
		throw new CLI\Command\CommandInvokedException( $e->getMessage(), 1 );
	}
});

$usageProfileUpdate = "update [-y|--yes] <app>/<profile>";
$subcommandProfileUpdate->setUsage( $usageProfileUpdate );

$subcommandProfileUpdate->registerOption( 'y' );
$subcommandProfileUpdate->registerOption( 'yes' );

// Register subcommands
$commandProfile->addSubcommand( $subcommandProfileDeploy );
$commandProfile->addSubcommand( $subcommandProfileUpdate );

return $commandProfile;
