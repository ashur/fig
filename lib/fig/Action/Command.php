<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Fig;

class Command extends Action
{
	/**
	 * @var	boolean
	 */
	protected $allowPrivilegeEscalation = true;

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
		parent::__construct( $properties );

		Fig::validateRequiredKeys( $properties, ['command'] );

		$this->command = $properties['command'];
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @param	string	$username
	 * @param	string	$password
	 * @return	array
	 */
	public function execute( $username=null, $password=null )
	{
		if( $this->escalatePrivileges )
		{
			if( !is_null( $password ) )
			{
				$command = "echo '{$password}' | sudo -S {$this->command}";
			}
			else
			{
				$command = "sudo {$this->command}";
			}
		}
		else
		{
			$command = $this->command;
		}

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
		$title = $this->name;
		if( $this->escalatePrivileges )
		{
			$title = "{$title} 🔑 ";
		}

		return $title;
	}
}
