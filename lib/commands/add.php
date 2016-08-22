<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI\Command;
use Huxtable\CLI\Format;
use Huxtable\CLI\Input;

/**
 * @command		add
 * @desc		Create apps and profiles
 * @usage		add <app>[/<profile>]
 */
$commandAdd = new Command( 'add', 'Create apps and profiles', function( $query )
{
	$fig = new Fig();

	$params = parseQuery( $query, '/', ['app','profile'] );

	try
	{
		$app = $fig->createApp( $params['app'] );
	}
	/* App already exists */
	catch( \Exception $e )
	{
		/* User was only trying to create a new app */
		if( !isset( $params['profile'] ) )
		{
			throw new Command\CommandInvokedException( $e->getMessage(), 1 );
		}
	}

	/* No new profile specified */
	if( !isset( $params['profile'] ) )
	{
		$stringAppName = new Format\String( $params['app'] );
		$stringAppName->foregroundColor( 'green' );

		$shouldAddProfile = strtolower( Input::prompt( "Create a new profile for {$stringAppName}? (y/n)" ) ) == 'y';
		if( $shouldAddProfile )
		{
			$profileName = Input::prompt( 'Profile name:' );
		}
	}
	else
	{
		$shouldAddProfile = true;
		$profileName = $params['profile'];
	}

	if( $shouldAddProfile )
	{
		try
		{
			/* Create a profile */
			$fig->createProfile( $params['app'], $profileName );
		}
		catch( \Exception $e )
		{
			throw new Command\CommandInvokedException( $e->getMessage(), 1 );
		}
	}
});

$commandAdd->setUsage( 'add <app>[/<profile>]' );

return $commandAdd;
