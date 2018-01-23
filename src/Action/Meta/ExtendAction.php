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
	 * @param	string	$extendedProfileName
	 *
	 * @return	void
	 */
	public function __construct( string $extendedProfileName )
	{
		$this->extendedProfileName = $extendedProfileName;
	}

	/**
	 * Returns name of profile to be extended
	 *
	 * @return	string
	 */
	public function getExtendedProfileName() : string
	{
		return $this->extendedProfileName;
	}
}
