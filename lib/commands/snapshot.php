<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;
use Cranberry\CLI\Input;

/**
 * @command		snapshot
 * @desc		Update profile assets to mirror live files
 * @usage		snapshot [-y|--yes] <app>/<profile>
 * @options		-y, --yes
 */
$command = new Command\Command( 'snapshot', 'Update profile assets to mirror live files', function( $query )
{
	try
	{
		$params = parseQuery( $query, '/', ['app','profile'] );

		if( is_null( $this->getOptionValue( 'y' ) ) && is_null( $this->getOptionValue( 'yes' ) ) )
		{
			$continue = Input::prompt( "Are you sure you want to overwrite the '{$query}' profile assets? (y/n)", true );
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

$usage = "snapshot [-y|--yes] <app>/<profile>";
$command->setUsage( $usage );

return $command;
