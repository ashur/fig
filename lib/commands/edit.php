<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI\Command;
use Huxtable\CLI\Format;
use Huxtable\CLI\Input;
use Huxtable\Core\File;

/**
 * @command		edit
 * @desc		Edit a profile
 * @usage		edit <app>/<profile>
 */
$commandEdit = new Command( 'edit', 'Edit a profile', function( $query )
{
	$fig = new Fig();

	$params = parseQuery( $query, '/', ['app','profile'] );

	if( !isset( $params['profile'] ) )
	{
		throw new Command\IncorrectUsageException( $this->getUsage(), 1 );
	}

	if( getenv( 'EDITOR' ) == false )
	{
		throw new Command\CommandInvokedException( '$EDITOR not set.' );
	}

	/* Ensure the file exists */
	$pathProfile = sprintf( '%s/%s/%s.yml', Fig::DIR_FIG, $params['app'], $params['profile'] );
	$fileProfile = new File\File( $pathProfile );

	if( !$fileProfile->exists() )
	{
		throw new Command\CommandInvokedException( "Profile not found '{$params['app']}/{$params['profile']}'." );
	}

	$descriptors = array
	(
		array( 'file', '/dev/tty', 'r' ),
        array( 'file', '/dev/tty', 'w' ),
        array( 'file', '/dev/tty', 'w ')
	);

	$process = proc_open( "\$EDITOR {$fileProfile}", $descriptors, $pipes );
	proc_close( $process );
});

$commandEdit->setUsage( 'edit <app>/<profile>' );

return $commandEdit;
