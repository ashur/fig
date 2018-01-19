<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action\AbstractAction;
use Fig\Exception;
use Fig\Shell\Shell;

abstract class AbstractDefaultsAction extends AbstractAction
{
	use \Fig\Action\Shell\DeployTrait;

	/**
	 * @var	string
	 */
	protected $domain;

	/**
	 * @var	string
	 */
	protected $key;

	/**
	 * @var	string
	 */
	protected $methodName;

	/**
	 * @var	string
	 */
	protected $type = 'Defaults';

	/**
	 * Returns domain
	 *
	 * @return	string
	 */
	public function getDomain() : string
	{
		return $this->replaceVariablesInString( $this->domain );
	}

	/**
	 * Returns defaults key
	 *
	 * @throws	OutOfBoundsException	If key is undefined
	 *
	 * @return	string
	 */
	public function getKey() : string
	{
		if( $this->key == null )
		{
			throw new \OutOfBoundsException( 'Key is undefined' );
		}

		return $this->replaceVariablesInString( $this->key );
	}

	/**
	 * Returns method string as action subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return $this->methodName;
	}

	/**
	 * Returns whether key is defined
	 *
	 * @return	bool
	 */
	public function hasKey() : bool
	{
		return $this->key != null;
	}
}
