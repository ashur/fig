<?php

use Cranberry\CLI\Command;

$optionHelp = new Command\ApplicationOption( 'help', 'Display the help menu', function()
{
	$appOptionsContent = '';
	foreach( $this->app->options as $appOption )
	{
		$appOptionName = $appOption->name;
		$appOptionsContent .= " [{$appOptionName}]";
	}
	$appOptionsContent = trim( $appOptionsContent );

	$commandName = $this->getCommandName();
	if( is_null( $commandName ) )
	{
		$this->output->wrappedLine( "usage: {$this->app->name} {$appOptionsContent} <command> [<args>]", 10 );
		$this->output->line( '' );

		$this->output->line( 'Commands are:' );
		foreach( $this->app->commands as $command )
		{
			$commandName = $command->name;

			$commandDescription = $command->description;
			$commandDescription = str_replace( '{app}', $this->app->name, $commandDescription );

			$commandLine = sprintf( '   %-10s %s', $commandName, $commandDescription );
			$this->output->wrappedLine( $commandLine, 14 );
		}

		$this->output->line( '' );
		$this->output->line( "See '{$this->app->name} --help <command>' to read about a specific command." );
	}
	else
	{
		$commandExists = false;
		foreach( $this->app->commands as $command )
		{
			if( $command->name == $commandName )
			{
				$commandExists = true;
				break;
			}
		}

		if( !$commandExists )
		{
			throw new Command\CommandInvokedException( "'{$commandName}' is not a {$this->app->name} command. See '{$this->app->name} --help'", 1 );
		}

		if( count( $command->subcommands ) == 0 )
		{
			$this->output->line( sprintf( 'usage: %s %s', $this->app->name, $command->usage ) );
			return;
		}
	}
});

$optionHelp->useAsApplicationDefault( true );

return $optionHelp;
