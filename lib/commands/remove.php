<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;
use Cranberry\CLI\Input;

/**
 * @command		remove
 * @desc		Delete apps or profiles
 * @usage		remove <app>[/<profile>]
 */
$command = new Command\Command( 'remove', 'Delete apps or profiles', function( $query )
{
	$params = parseQuery( $query, '/', ['app','profile'] );

	$stringName = implode( '/', $params );

	/* Make sure the requested resource exists before proceeding */
	try
	{
		$app = $this->fig->getApp( $params['app'] );

		if( isset( $params['profile'] ) )
		{
			$profile = $app->getProfile( $params['profile'] );
		}
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}

	/*
	 * Confirm deletion
	 */
	if( isset( $params['profile'] )	)
	{
		$type = 'profile';
	}
	else
	{
		$type = 'app';
	}

	$confirmationMessage = "Are you sure you want to delete the {$type} '{$stringName}'? This cannot be undone (y/n)";
	$shouldDelete = strtolower( Input::prompt( $confirmationMessage ) ) == 'y';
	if( !$shouldDelete )
	{
		return;
	}

	/* Perform the deletion */
	if( isset( $params['profile'] ) )
	{
		$this->fig->deleteProfile( $params['app'], $params['profile'] );
	}
	else
	{
		$this->fig->deleteApp( $params['app'] );
	}
});

$command->registerAlias( 'rm' );
$command->setUsage( 'add <app>[/<profile>]' );

return $command;
