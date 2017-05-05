<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;
use Cranberry\CLI\Input;

/**
 * @command		deploy
 * @desc		Deploy a profile
 * @usage		deploy <app>/<profile>
 */
$commandDeploy = new Command\Command( 'deploy', 'Deploy a profile', function( $query )
{
	$params = parseQuery( $query, '/', ['app','profile'] );

	if( !isset( $params['profile'] ) )
	{
		throw new Command\IncorrectUsageException( $this->getUsage(), 1 );
	}

	try
	{
		$this->fig->deployProfile( $params['app'], $params['profile'] );
		echo PHP_EOL;
	}
	catch( Command\IncorrectUsageException $e )
	{
		throw new Command\IncorrectUsageException( $this->getUsage(), 1 );
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}
});

$commandDeploy->setUsage( 'deploy <app>/<profile> [options]' );

return $commandDeploy;
