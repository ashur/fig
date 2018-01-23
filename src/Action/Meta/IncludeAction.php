<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Meta;

use Fig\Action\AbstractAction;

class IncludeAction extends AbstractAction
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
	 * @param	string	$includedProfileName
	 *
	 * @param	array	$arguments
	 *
	 * @return	void
	 */
	public function __construct( string $includedProfileName, array $arguments=[] )
	{
		$this->arguments = $arguments;
		$this->includedProfileName = $includedProfileName;
	}

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
}
