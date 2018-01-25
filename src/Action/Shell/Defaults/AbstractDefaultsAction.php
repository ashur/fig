<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action\AbstractDeployableAction;
use Fig\Engine;
use Fig\Exception;
use Fig\Shell\Shell;

abstract class AbstractDefaultsAction extends AbstractDeployableAction
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
		return Engine::renderTemplate( $this->domain, $this->vars );
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

		return Engine::renderTemplate( $this->key, $this->vars );
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
