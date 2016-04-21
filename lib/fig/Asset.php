<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\Core\File;

class Asset implements \JsonSerializable
{
	const SKIP    = 0;
	const CREATE  = 1;
	const REPLACE = 2;
	const DELETE  = 4;

	/**
	 * @var	int
	 */
	protected $action = self::SKIP;

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $source;

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $target;

	/**
	 * @param	Huxtable\Core\File\File	$target
	 * @return	void
	 */
	public function __construct( File\File $target )
	{
		$this->target = $target;
	}

	/**
	 * Set the action to create
	 *
	 * @return	void
	 */
	public function create()
	{
		$this->action = self::CREATE;
	}

	/**
	 * Set the action to delete
	 *
	 * @return	void
	 */
	public function delete()
	{
		$this->action = self::DELETE;
	}

	/**
	 * Perform the action as specified
	 *
	 * @return	void
	 */
	public function deploy()
	{
		switch( $this->action )
		{
			case self::SKIP:
				break;

			case self::CREATE:
				$this->target->create();
				break;

			case self::REPLACE:
				$this->source->copyTo( $this->target );
				break;

			case self::DELETE:
				$this->target->delete();
				break;
		}
	}

	/**
	 * @return	int
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @return	Huxtable\Core\File\File|Directory
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @return	Huxtable\Core\File\File|Directory
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * Set the action to replace
	 *
	 * @param	Huxtable\Core\File\File		$dirSource
	 * @return	void
	 */
	public function replaceWith( File\File $dirSource )
	{
		$this->action = self::REPLACE;
		$this->source = $dirSource;
	}

	/**
	 * Set the action to skip
	 *
	 * @return	void
	 */
	public function skip()
	{
		$this->action = self::SKIP;
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		$data['target'] = $this->target->getPathname();

		switch( $this->action )
		{
			case self::SKIP:
				$data['action'] = 'skip';
				break;

			case self::CREATE:
				$data['action'] = 'create';
				break;

			case self::REPLACE:
				$data['action'] = 'replace';
				break;

			case self::DELETE:
				$data['action'] = 'delete';
				break;

			default:
				$data['action'] = 'skip';
				break;
		}

		if( !is_null( $this->source ) )
		{
			$data['source'] = $this->source->getPathname();
		}

		return $data;
	}
}
