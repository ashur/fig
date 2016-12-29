<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Command;
use Cranberry\CLI\Input;
use Cranberry\Core\File;

/**
 * @param	Cranberry\Core\File\Directory	$directory
 * @return	void
 */
function validateDirectory( File\Directory $directory )
{
	/* ...n'existe pas */
	if( !$directory->exists() )
	{
		throw new \Exception( "'{$directory}' does not exist." );
	}

	/* ...has permissions problem */
	if( !$directory->isReadable() )
	{
		throw new \Exception( "'{$directory}' is not readable." );
	}
	if( !$directory->isWritable() )
	{
		throw new \Exception( "'{$directory}' is not writeable." );
	}

	return true;
}

/**
 * @name			install
 * @description		Symlink 'fig' to a convenient path
 * @usage			install
 */
$command = new Command\Command( 'install', 'Symlink \'fig\' to a convenient path', function()
{
	$sourceFile = $this->app->applicationDirectory
		->childDir( 'bin' )
		->child( 'fig' );

	$defaultPath = '/usr/local/bin';
	$userPath = $this->getOptionValue( 'dir' );

	/* Default destination */
	if( is_null( $userPath ) )
	{
		$shouldPrompt = true;
		$destinationDirectory = new File\Directory( $defaultPath );
	}
	/* User-specified destination */
	else
	{
		$shouldPrompt = false;

		try
		{
			$destinationDirectory = new File\Directory( $userPath );
		}
		catch( \Exception $e )
		{
			throw new Command\CommandInvokedException( 'Invalid location: ' . $e->getMessage(), 1 );
		}
	}

	$hasPrompted = false;
	$didValidate = false;

	while( !$didValidate )
	{
		try
		{
			$didValidate = validateDirectory( $destinationDirectory );

			/* Is destination directory on $PATH? */
			$envPathDirs = explode( ':', getenv( 'PATH' ) );
			if( !in_array( $destinationDirectory, $envPathDirs ) )
			{
				$shouldContinue = Input::prompt( "Target directory '{$destinationDirectory}' is not on \$PATH. Continue? (y/n)", true );
				$shouldContinue = strtolower( $shouldContinue );

				/* User chose not to continue */
				if( $shouldContinue != 'y' && $shouldContinue != 'yes' )
				{
					exit( 1 );
				}
			}
		}
		catch( \Exception $e )
		{
			if( $shouldPrompt )
			{
				if( $hasPrompted )
				{
					echo 'Error: ' . $e->getMessage() . PHP_EOL;
				}

				$userPath = Input::prompt( "Symlink 'fig' to directory:", true );

				try
				{
					$destinationDirectory = new File\Directory( $userPath );
				}
				catch( \Exception $e )
				{
					throw new Command\CommandInvokedException( 'Invalid location: ' . $e->getMessage(), 1 );
				}
			}
			else
			{
				throw new Command\CommandInvokedException( 'Invalid location: ' . $e->getMessage(), 1 );
			}
		}
	}

	/* Does target file already exist? */
	$targetFile = $destinationDirectory->child( 'fig' );

	if( $targetFile->exists() )
	{
		if( $targetFile->isLink() )
		{
			if( $targetFile->getLinkTarget() == $sourceFile->getRealPath() )
			{
				echo "Already installed." . PHP_EOL;
				return;
			}
		}

		throw new Command\CommandInvokedException( "Target file '{$targetFile}' already exists." );
	}

	symlink( $sourceFile, $targetFile );

	echo 'Installed.' . PHP_EOL;
});

$command->registerOption( 'dir' );
$command->setUsage( 'install [--dir=<dir>]' );

return $command;
