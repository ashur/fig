<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI;
use Huxtable\CLI\Input;

/**
 * @command		deploy
 * @desc		Deploy a profile
 * @usage		deploy <app>/<profile>
 */
$commandDeploy = new CLI\Command( 'deploy', 'Deploy a profile', function( $query )
{
	$fig = new Fig();

	try
	{
		$params = parseQuery( $query, '/', ['app','profile'] );
		$fig->deployProfile( $params['app'], $params['profile'] );
		echo PHP_EOL;
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

$usageDeploy = "deploy <app>/<profile>";
$commandDeploy->setUsage( $usageDeploy );

return $commandDeploy;
