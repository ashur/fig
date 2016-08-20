<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use \Huxtable\CLI;
use \Huxtable\Core\File;

/**
 * @param	string	$query			ex., "<app>/<profile>" or "<app>:<command>"
 * @param	string	$delimeter		ex., "/" or ":"
 * @param	array	$labels			ex., ['app','profile']
 * @return	array
 */
function parseQuery( $query, $delimiter, array $labels )
{
	$params = [];
	$queryPieces = explode( $delimiter, $query );

	$expectedPiecesCount = count( $labels );
	if( count( $queryPieces ) == $expectedPiecesCount )
	{
		for( $i = 0; $i < $expectedPiecesCount; $i++ )
		{
			if( empty( $queryPieces[$i] ) )
			{
				throw new CLI\Command\IncorrectUsageException();
			}

			$params[$labels[$i]] = $queryPieces[$i];
		}

		return $params;
	}

	throw new CLI\Command\IncorrectUsageException();
}

/*
 * Commands
 */
/**
 * @command		deploy
 * @desc		Deploy a profile (an alias for 'command run')
 * @usage		deploy <app>/<profile>
 */
$commandDeploy = new CLI\Command( 'deploy', 'Deploy a profile', function( $query )
{
	$fig = new Fig();

	try
	{
		$params = parseQuery( $query, '/', ['app','profile'] );
		$fig->deployProfile( $params['app'], $params['profile'] );
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

/**
 * @command		run
 * @desc		Run a command (an alias for 'command run')
 * @usage		run <app>:<command>
 */
$commandRun = new CLI\Command( 'run', 'Run a command', function( $query )
{
	$fig = new Fig();

	try
	{
		$params = parseQuery( $query, ':', ['app','command'] );
		$fig->executeCommand( $params['app'], $params['command'] );
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

$usageRun = "run <app>:<command>";
$commandRun->setUsage( $usageRun );
