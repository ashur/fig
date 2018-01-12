<?php

/*
 * This file is part of Fig
 */
namespace Fig\Shell;

class Result
{
	/**
	 * The exit status of the executed command
	 *
	 * @var	int
	 */
	protected $exitCode;

	/**
	 * Array of lines output by executing the command
	 *
	 * @var	array
	 */
	protected $output;

	/**
	 * @param	array	$output		Array of lines output by executing the command. Trailing whitespace is not included.
	 *
	 * @param	int		$exitCode	The exit status of the executed command.
	 *
	 * @return	void
	 */
	public function __construct( array $output, int $exitCode )
	{
		$this->output = $output;
		$this->exitCode = $exitCode;
	}

	/**
	 * Returns exit code
	 *
	 * @return	int
	 */
	public function getExitCode() : int
	{
		return $this->exitCode;
	}

	/**
	 * Returns output array
	 *
	 * @return	array
	 */
	public function getOutput() : array
	{
		return $this->output;
	}
}
