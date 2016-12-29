<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Core\File;

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
	 * @var	string
	 */
	protected $name;

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
	public function __construct( $name, File\File $target )
	{
		$this->name = $name;
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
				$this->target->delete();
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
	 * @return	string
	 */
	public function getActionName()
	{
		switch( $this->action )
		{
			case self::SKIP:
				return 'skip';
				break;

			case self::CREATE:
				return 'create';
				break;

			case self::REPLACE:
				return 'replace';
				break;

			case self::DELETE:
				return 'delete';
				break;
		}
	}

	/**
	 * @param	array							$data
	 * @param	Huxtable\Core\File\Directory	$dirAssets
	 * @return	self
	 */
	static public function getInstanceFromData( array $data, File\Directory $dirAssets=null )
	{
		// Check required fields
		$requiredFields = ['name','action','target'];
		foreach( $requiredFields as $requiredField )
		{
			if( !isset( $data[$requiredField] ) )
			{
				throw new \Exception( "Invalid profile: missing asset field '{$requiredField}'" );
			}
		}

		$target = File\File::getTypedInstance( $data['target'] );
		$asset = new self( $data['name'], $target );

		switch( $data['action'] )
		{
			case 'skip':
				$asset->skip();
				break;

			case 'create':
				$asset->create();
				break;

			case 'replace':
				if( !isset( $data['source'] ) )
				{
					throw new \Exception( "Invalid profile: missing asset field '{$requiredField}'" );
				}
				if( is_null( $dirAssets ) )
				{
					throw new \BadFunctionCallException( "Bad function call: missing argument '\$dirAssets'" );
				}

				$fileTarget = File\File::getTypedInstance( $data['target'] );
				if( $fileTarget->isDir() )
				{
					$fileSource = $dirAssets->childDir( $data['source'] );
				}
				else
				{
					$fileSource = $dirAssets->child( $data['source'] );
				}

				$asset->replaceWith( $fileSource );
				break;

			case 'delete':
				$asset->delete();
				break;

			default:
				$asset->skip();
				break;
		}

		return $asset;
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
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
	 * @return	boolean
	 */
	public function replaceSourceWithTarget()
	{
		// Only files using the 'replace' action will have a source to replace
		if( $this->action != self::REPLACE )
		{
			return false;
		}

		// If the target doesn't exist, there's nothing to update with. Bail.
		if( !$this->target->exists() )
		{
			return false;
		}

		$this->source->delete();
		$this->target->copyTo( $this->source );

		return true;
	}

	/**
	 * Set the action to replace
	 *
	 * @param	Huxtable\Core\File\File		$fileSource
	 * @return	void
	 */
	public function replaceWith( File\File $fileSource )
	{
		$this->action = self::REPLACE;
		$this->source = $fileSource;
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

		$data['target'] = $this->target->getPathname();

		// Only serialize the filename (path is always relative to the Profile directory)
		if( !is_null( $this->source ) )
		{
			$data['source'] = $this->source->getBasename();
		}

		return $data;
	}
}
