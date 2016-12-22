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
 * 				add <app> --url=<url>
 */
$commandAdd = new Command( 'add', 'Create apps and profiles', function( $query )
{
	$fig = new Fig();
	$params = parseQuery( $query, '/', ['app','profile'] );

	$shouldAddProfile = isset( $params['profile'] );

	try
	{
		$app = $fig->getApp( $params['app'] );

		/*
		 * App exists
		 */
		$url = null;

		/* User only attempting to create app */
		if( !isset( $params['profile'] ) )
		{
			throw new Command\CommandInvokedException( "App '{$params['app']}' already exists. See 'fig show'.", 1 );
		}
		/* User attempting to create profile */
		else
		{
			$profileName = $params['profile'];
		}
	}

	/*
	 * App does not exist
	 */
	catch( \OutOfRangeException $e )
	{
		/* Create using repository */
		if( ($url = $this->getOptionValue( 'url' )) !== null )
		{
			if( isset( $params['profile'] ) )
			{
				throw new Command\CommandInvokedException( 'Profiles cannot be created from repositories. See \'fig help add\'.', 1 );
			}

			$shouldAddProfile = false;
			$didCloneRepository = $fig->createAppFromRepository( $params['app'], $url );

			if( !$didCloneRepository )
			{
				exit( 1 );
			}
		}

		/* Create from scratch */
		else
		{
			$app = $fig->createApp( $params['app'] );

			$stringAppName = new Format\String( $params['app'] );
			$stringAppName->foregroundColor( 'green' );

			if( !$shouldAddProfile )
			{
				$shouldAddProfile = strtolower( Input::prompt( "Create a new profile for {$stringAppName}? (y/n)" ) ) == 'y';
				if( $shouldAddProfile )
				{
					$profileName = Input::prompt( 'Profile name:' );
				}
			}
			else
			{
				$profileName = $params['profile'];
			}
		}
	}

	if( $shouldAddProfile )
	{
		try
		{
			/* Incorrectly trying to create from repository */
			if( !is_null( $url ) )
			{
				throw new Command\CommandInvokedException( 'Profile creation from remote repository not supported. See \'fig help add\'.', 1 );
			}

			/* Create a profile */
			$fig->createProfile( $params['app'], $profileName );
		}
		catch( \Exception $e )
		{
			throw new Command\CommandInvokedException( $e->getMessage(), 1 );
		}
	}
});

$commandAdd->registerAlias( 'create' );
$commandAdd->registerOption( 'url' );

$commandAdd->setUsage( "add <app>[/<profile>]\n       fig add <app> --url=<url>" );

return $commandAdd;
