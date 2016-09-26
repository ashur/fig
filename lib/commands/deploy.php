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

	$params = parseQuery( $query, '/', ['app','profile'] );

	if( !isset( $params['profile'] ) )
	{
		throw new CLI\Command\IncorrectUsageException( $this->getUsage(), 1 );
	}

	/* Privilege escalation */
	$fig->setSudoPassword( $this->getOptionValue( 'sudo-pass' ) );

	try
	{
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

$commandDeploy->registerOption( 'sudo-pass' );

$usageDeploy = <<<USAGE
deploy <app>/<profile> [options]

OPTIONS
     --sudo-pass=<password>
         privilege escalation password

USAGE;

$commandDeploy->setUsage( $usageDeploy );

return $commandDeploy;
