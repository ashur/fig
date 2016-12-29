<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;

/**
 * @command		show
 * @desc		List apps and their profiles
 * @usage		show
 */
$command = new Command\Command( 'show', 'List apps and their profiles', function()
{
	try
	{
		$apps = $this->fig->getApps();
	}
	catch( \Exception $e )
	{
		throw new Command\CommandInvokedException( $e->getMessage(), 1 );
	}

	$output = listApps( $apps );
	return $output->flush();
});

$command->registerAlias( 'ls' );

return $command;
