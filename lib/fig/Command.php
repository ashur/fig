<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\Core\File;

class Command implements \JsonSerializable
{
	/**
	 * @var	string
	 */
	protected $command;

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
		exec( $this->command, $output, $exitCode );

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
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		return [
			'command' => $this->command,
			'name' => $this->name
		];
	}
}
