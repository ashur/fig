<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;

class ExtendAction extends BaseAction
{
	/**
	 * @var	string
	 */
	protected $extendedProfileName;

	/**
	 * @param	string	$name
	 *
	 * @param	string	$extendedProfileName
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $extendedProfileName )
	{
		$this->name = $name;

		$this->extendedProfileName = $extendedProfileName;
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
	 * Returns name of profile to be extended
	 *
	 * @return	string
	 */
	public function getExtendedProfileName() : string
	{
		return $this->extendedProfileName;
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
