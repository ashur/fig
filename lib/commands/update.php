<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;
use Cranberry\CLI\Input;

/**
 * @command		Update profile assets from source
 * @desc		update
 * @usage		update [-y|--yes] <app>/<profile>
 * @options		-y, --yes
 */
$command = new Command\Command( 'update', 'Update profile assets from source', function( $query )
{
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

		$this->fig->updateProfileAssetsFromTarget( $params['app'], $params['profile'] );
	}
	catch( Command\Command\IncorrectUsageException $e )
	{
		throw new Command\Command\IncorrectUsageException( $this->getUsage(), 1 );
	}
	catch( \Exception $e )
	{
		throw new Command\Command\CommandInvokedException( $e->getMessage(), 1 );
	}
});

$command->registerOption( 'y' );
$command->registerOption( 'yes' );

$usage = "update [-y|--yes] <app>/<profile>";
$command->setUsage( $usage );

return $command;
