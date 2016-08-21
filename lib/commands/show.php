<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI;
use Huxtable\CLI\Command;

/**
 * @command		app
 * @desc		List apps and their profiles
 * @usage		app
 */
$commandShow = new CLI\Command( 'show', 'List apps and their profiles', function()
{
	$fig = new Fig();

	try
	{
		$apps = $fig->getApps();
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}

	$output = listApps( $apps );
	return $output->flush();
});

$commandShow->registerAlias( 'ls' );

return $commandShow;
