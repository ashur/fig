<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\Core\File;

class Command
{
	/**
	 * @var	string
	 */
	protected $command;

	/**
	 * @var	boolean
	 */
	public $ignoreErrors=false;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @param	string		$name
	 * @param	string		$command
	 * @return	void
	 */
	public function __construct( $name, $command )
	{
		$this->name = $name;
		$this->command = $command;
	}

	/**
	 * @return	array
	 */
	public function exec()
	{
		exec( "{$this->command} 2>&1", $output, $exitCode );

		$result['output']   = $output;
		$result['exitCode'] = $exitCode;

		return $result;
	}

	/**
	 * @return	string
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 * @param	array	$data
	 * @return	self
	 */
	static public function getInstanceFromData( array $data )
	{
		$command = new self( $data['name'], $data['command'] );

		/*
		 * Properties
		 */
		if( isset( $data['ignore_errors'] ) )
		{
			$command->ignoreErrors = true;
		}

		return $command;
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}
}
