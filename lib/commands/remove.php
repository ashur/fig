<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI\Command;
use Huxtable\CLI\Format;
use Huxtable\CLI\Input;

/**
 * @command		remove
 * @desc		Delete apps or profiles
 * @usage		remove <app>[/<profile>]
 */
$commandRemove = new Command( 'remove', 'Delete apps or profiles', function( $query )
{
	$fig = new Fig();

	$params = parseQuery( $query, '/', ['app','profile'] );

	$stringName = implode( '/', $params );

	/* Make sure the requested resource exists before proceeding */
	try
	{
		$app = $fig->getApp( $params['app'] );

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
		$fig->deleteProfile( $params['app'], $params['profile'] );
	}
	else
	{
		$fig->deleteApp( $params['app'] );
	}
});

$commandRemove->addAlias( 'rm' );
$commandRemove->setUsage( 'add <app>[/<profile>]' );

return $commandRemove;
