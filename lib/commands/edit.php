<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;
use Cranberry\Core\File;

/**
 * @command		edit
 * @desc		Edit a profile
 * @usage		edit <app>/<profile>
 */
$command = new Command\Command( 'edit', 'Edit a profile', function( $query )
{
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
	$pathProfile = sprintf( '%s/%s/%s.yml', $this->fig->getFigDirectory(), $params['app'], $params['profile'] );
	$fileProfile = new File\File( $pathProfile );

	if( !$fileProfile->exists() )
	{
		throw new Command\CommandInvokedException( "Profile not found '{$params['app']}/{$params['profile']}'." );
	}

	/*
	 * nano-friendly solution from http://stackoverflow.com/questions/3614715/open-vim-from-php-cli/15832158#15832158
	 */
	$descriptors = array
	(
		array( 'file', '/dev/tty', 'r' ),
        array( 'file', '/dev/tty', 'w' ),
        array( 'file', '/dev/tty', 'w ')
	);

	$process = proc_open( "\$EDITOR {$fileProfile}", $descriptors, $pipes );
	proc_close( $process );
});

$command->setUsage( 'edit <app>/<profile>' );

return $command;
