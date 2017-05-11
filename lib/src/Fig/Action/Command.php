<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig;

class Command extends Action
{
	/**
	 * @var	int
	 */
	public $command;

	/**
	 * @var	string
	 */
	public $type = 'Command';

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		/*
		 * Define properties
		 */
		$this->defineProperty( 'command', true, 'self::isStringish', function( $value )
		{
			/*
			 * Sanitize command string
			 */
			$sanitizedCommand = $value;
			$sanitizedCommand = trim( $sanitizedCommand );

			/* Strip trailing semicolon, which subverts output/error handling */
			if( substr( $sanitizedCommand, -1 ) == ';' )
			{
				$sanitizedCommand = substr( $sanitizedCommand, 0, strlen( $sanitizedCommand ) - 1 );
			}

			$this->command = $sanitizedCommand;

		});

		parent::__construct( $properties );
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @return	array
	 */
	public function execute()
	{
		/* Replace variables */
		$command = Fig\Fig::replaceVariables( $this->command, $this->variables );

		/* Results */
		exec( "{$command} 2>&1", $output, $exitCode );

		$result['error'] = $exitCode != 0;
		$result['output'] = $output;

		/* Modify Output */
		if( $this->ignoreOutput )
		{
			$result['output'] = null;
		}
		if( $this->ignoreErrors && $result['error'] == true )
		{
			$result['error'] = false;
			$result['output'] = null;
		}

		return $result;
	}

	/**
	 * @return	string
	 */
	public function getTitle()
	{
		$title = Fig\Fig::replaceVariables( $this->name, $this->variables );
		return $title;
	}
}
