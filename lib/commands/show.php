<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;

/**
 * @command		show
 * @desc		List apps and their profiles
 * @usage		show [-l] <app>
 */
$command = new Command\Command( 'show', 'List apps and their profiles', function( $app=null )
{
	$listHiddenItems = $this->getOptionValue( 'a' );
	$longListing = $this->getOptionValue( 'l' );

	try
	{
		/* List apps */
		if( is_null( $app ) )
		{
			$apps = $this->fig->getApps();
			$output = listApps( $apps, $listHiddenItems, $longListing );
		}
		/* List a single app's profiles */
		else
		{
			$app = $this->fig->getApp( $app );
			$output = listProfiles( $app, $listHiddenItems, $longListing );
		}
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}

	return $output->flush();
});

$command->registerOption( 'a' );	// List entries starting with .
$command->registerOption( 'l' );	// Long listing
$command->registerAlias( 'ls' );

$usageShow = <<<USAGE
show [options] [<app>]

OPTIONS
     -a
         List entries starting with .

     -l
         List in long format.

USAGE;

$command->setUsage( $usageShow );

return $command;
