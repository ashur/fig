<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;
use Cranberry\CLI\Format;
use Cranberry\CLI\Input;

/**
 * @command		add
 * @desc		Create apps and profiles
 * @usage		add <app>[/<profile>]
 * 				add <app> --url=<url>
 */
$command = new Command\Command( 'add', 'Create apps and profiles', function( $query )
{
	$params = parseQuery( $query, '/', ['app','profile'] );
	if( !isset( $params['app'] ) )
	{
		throw new Command\CommandInvokedException( "Missing app name in '{$query}'. See 'fig --help add'.", 1 );
	}

	$shouldAddProfile = isset( $params['profile'] );

	try
	{
		$app = $this->fig->getApp( $params['app'] );

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
				throw new Command\CommandInvokedException( 'Profiles cannot be created from repositories. See \'fig --help add\'.', 1 );
			}

			$shouldAddProfile = false;
			$didCloneRepository = $this->fig->createAppFromRepository( $params['app'], $url );

			if( !$didCloneRepository )
			{
				exit( 1 );
			}
		}

		/* Create from scratch */
		else
		{
			try
			{
				$app = $this->fig->createApp( $params['app'] );
			}
			catch( \InvalidArgumentException $e )
			{
				throw new Command\CommandInvokedException( $e->getMessage(), 1 );
			}

			$stringAppName = new Output\FormattedString( $params['app'] );
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
				throw new Command\CommandInvokedException( 'Profile creation from remote repository not supported. See \'fig --help add\'.', 1 );
			}

			/* Create a profile */
			$actionContents = <<<ACTION
# A command example
- name: hello
  command: echo 'hello!'
ACTION;

			if( $this->getOptionValue( 'extend' ) != null )
			{
				$parentProfileName = $this->getOptionValue( 'extend' );

			$actionContents = <<<ACTION
- extend: {$parentProfileName}
ACTION;
			}

			$this->fig->createProfile( $params['app'], $profileName, $actionContents );
		}
		catch( \Exception $e )
		{
			throw new Command\CommandInvokedException( $e->getMessage(), 1 );
		}
	}
});

$command->registerAlias( 'create' );
$command->registerOption( 'extend' );
$command->registerOption( 'url' );

$commandUsage = <<<USAGE
add <app> [--url=<url>]
       fig add <app>/<profile> [--extend=<parent>]
USAGE;

$command->setUsage( $commandUsage );

return $command;
