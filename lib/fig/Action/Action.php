<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

abstract class Action
{
	/**
	 * @var	string
	 */
	protected $appName;

	/**
	 * @var	boolean
	 */
	protected $ignoreErrors = false;

	/**
	 * @var	boolean
	 */
	protected $ignoreOutput = false;

	/**
	 * @var	string
	 */
	public $name;

	/**
	 * @var	string
	 */
	protected $profileName = '';

	/**
	 * @var	string
	 */
	public $type = 'Action';

	/**
	 * @param	array	$properties
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @return	void
	 */
	public function __construct( array $properties, $appName, $profileName )
	{
		$this->name = $properties['name'];
		$this->appName = $appName;
		$this->profileName = $profileName;

		/* Ignore Errors & Output */
		if( isset( $properties['ignore_errors'] ) )
		{
			$this->ignoreErrors = true;
		}
		if( isset( $properties['ignore_output'] ) )
		{
			$this->ignoreOutput = true;
		}
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @return	array
	 */
	abstract public function execute();
}
