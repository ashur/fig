<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Meta;

use Fig\Action\AbstractAction;

class ExtendAction extends AbstractAction
{
	/**
	 * @var	string
	 */
	protected $extendedProfileName;

	/**
	 * @var	string
	 */
	protected $type = 'Extend';

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
	 * Returns extended profile name as subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return $this->getExtendedProfileName();
	}
}
