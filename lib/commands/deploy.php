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

	/*
	 * Prepare for actions which use 'sudo'
	 */
	$username = $this->getOptionValue( 'su-user' );
	if( is_null( $username ) )
	{
		$username = getenv( 'USER' );
	}

	$password = $this->getOptionValue( 'sudo-pass' );
	$params = parseQuery( $query, '/', ['app','profile'] );

	if( !isset( $params['profile'] ) )
	{
		throw new CLI\Command\IncorrectUsageException( $this->getUsage(), 1 );
	}

	try
	{
		$fig->deployProfile( $params['app'], $params['profile'], $username, $password );
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

$commandDeploy->registerOption( 'su-user' );
$commandDeploy->registerOption( 'sudo-pass' );

$usageDeploy = <<<USAGE
deploy <app>/<profile> [options]

OPTIONS
     --su-user=<user>
         desired sudo user (default is \$USER)

     --sudo-pass=<password>
         privilege escalation password

USAGE;

$commandDeploy->setUsage( $usageDeploy );

return $commandDeploy;
