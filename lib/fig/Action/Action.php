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
	protected $profileName;

	/**
	 * @var	string
	 */
	public $type = 'Action';

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		$this->name = $properties['name'];

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

	/**
	 * Called when adding action to profile
	 *
	 * @param	string	$appName
	 * @return	void
	 */
	public function setAppName( $appName )
	{
		$this->appName = $appName;
	}

	/**
	 * Called when adding action to profile
	 *
	 * @param	string	$profileName
	 * @return	void
	 */
	public function setProfileName( $profileName )
	{
		$this->profileName = $profileName;
	}
}
