<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;

class IncludeAction extends BaseAction
{
	/**
	 * @var	array
	 */
	protected $arguments;

	/**
	 * @var	string
	 */
	protected $includedProfileName;

	/**
	 * @var	string
	 */
	protected $type = 'Include';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$includedProfileName
	 *
	 * @param	array	$arguments
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $includedProfileName, array $arguments=[] )
	{
		$this->name = $name;

		$this->arguments = $arguments;
		$this->includedProfileName = $includedProfileName;
	}

	/**
	 * A stub; will not be called during profile deployment
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine ){}

	/**
	 * Returns arguments array
	 *
	 * @return	array
	 */
	public function getArguments() : array
	{
		return $this->arguments;
	}

	/**
	 * Returns name of profile to be included
	 *
	 * @return	string
	 */
	public function getIncludedProfileName() : string
	{
		return $this->includedProfileName;
	}

	/**
	 * A stub; will not be called during profile deployment
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function getSubtitle() : string {}
}
