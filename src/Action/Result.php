<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

class Result
{
	const STRING_STATUS_SUCCESS = 'OK';

	/**
	 * @var	bool
	 */
	protected $didError;

	/**
	 * @var	bool
	 */
	protected $ignoreErrors=false;

	/**
	 * @var	bool
	 */
	protected $ignoreOutput=false;

	/**
	 * @param	string	$output
	 *
	 * @param	bool	$didError
	 *
	 * @return	void
	 */
	public function __construct( string $output, bool $didError )
	{
		$this->output = $output;
		$this->didError = $didError;
	}

	/**
	 * Returns whether deployment resulted in an error
	 *
	 * @return	bool
	 */
	public function didError() : bool
	{
		if( $this->ignoreErrors )
		{
			return false;
		}

		return $this->didError;
	}

	/**
	 * Returns output
	 *
	 * @return	string
	 */
	public function getOutput() : string
	{
		if( $this->ignoreOutput )
		{
			return self::STRING_STATUS_SUCCESS;
		}

		return $this->output;
	}

	/**
	 * Specify whether errors should be ignored
	 *
	 * @param	bool	$ignoreErrors
	 *
	 * @return	void
	 */
	public function ignoreErrors( bool $ignoreErrors )
	{
		$this->ignoreErrors = $ignoreErrors;
	}

	/**
	 * Specify whether output should be ignored
	 *
	 * @param	bool	$ignoreOutput
	 *
	 * @return	void
	 */
	public function ignoreOutput( bool $ignoreOutput )
	{
		$this->ignoreOutput = $ignoreOutput;
	}
}
