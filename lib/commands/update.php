<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI;
use Huxtable\CLI\Command;
use Huxtable\CLI\Input;

/**
 * @command		update
 * @desc		Update profile assets from source
 * @usage		update [-y|--yes] <app>/<profile>
 * @options		-y, --yes
 */
$commandProfileUpdate = new CLI\Command( 'update', 'Update profile assets from source', function( $query )
{
	$fig = new Fig();

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

$commandProfileUpdate->registerOption( 'y' );
$commandProfileUpdate->registerOption( 'yes' );

$usageProfileUpdate = "update [-y|--yes] <app>/<profile>";
$commandProfileUpdate->setUsage( $usageProfileUpdate );

return $commandProfileUpdate;
