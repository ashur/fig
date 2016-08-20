<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Fig;
use Fig\Profile;
use Huxtable\Core;

class File extends Action
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
	protected $asset;

	/**
	 * @var	string
	 */
	protected $contents;

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $target;

	/**
	 * @var	string
	 */
	public $type = 'File';

	/**
	 * @param	array	$properties
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @return	void
	 */
	public function __construct( array $properties, $appName, $profileName )
	{
		parent::__construct( $properties, $appName, $profileName );

		/* Create */
		if( isset( $properties['file']['create'] ) )
		{
			$this->action = self::CREATE;
			$this->target = Core\File\File::getTypedInstance( $properties['file']['create'] );

			if( isset( $properties['file']['contents'] ) )
			{
				$this->contents = $properties['file']['contents'];
			}
		}

		/* Replace */
		if( isset( $properties['file']['replace'] ) )
		{
			Fig::validateRequiredKeys( $properties['file'], ['source'] );

			$this->action = self::REPLACE;
			$this->target = Core\File\File::getTypedInstance( $properties['file']['replace'] );
			$this->source = $properties['file']['source'];
		}

		/* Delete */
		if( isset( $properties['file']['delete'] ) )
		{
			$this->action = self::DELETE;
			$this->target = Core\File\File::getTypedInstance( $properties['file']['delete'] );
		}
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @return	array
	 */
	public function execute()
	{
		$didSucceed = true;

		/* Skip */
		if( $this->action == self::SKIP )
		{
			$actionName = 'skip';
		}

		/* Create */
		if( $this->action == self::CREATE )
		{
			$actionName = 'create';
			$didSucceed = $didSucceed && $this->target->create();

			if( !empty( $this->contents ) )
			{
				$didSucceed = $didSucceed && $this->target->putContents( $this->contents );
			}
		}

		/* Replace */
		if( $this->action == self::REPLACE )
		{
			$actionName = 'replace';
			$sourceFile = $this->getSourceFile();	// File or Directory

			if( !$sourceFile->exists() )
			{
				throw new \Exception( "Source missing: '{$pathSource}'" );
			}

			if( $this->target->exists() )
			{
				$this->target->delete();
			}

			$didSucceed = $didSucceed && $sourceFile->copyTo( $this->target );
		}

		/* Delete */
		if( $this->action == self::DELETE )
		{
			$actionName = 'delete';
			$this->target->delete();
		}

		/* Results */
		$result['title'] = "{$actionName} | {$this->name}";
		$result['error'] = !$didSucceed;
		$result['output'] = null;

		/* Modify Output */
		if( $this->ignoreOutput )
		{
			$result['output'] = null;
		}
		if( $this->ignoreErrors )
		{
			$result['error'] = false;
		}

		return $result;
	}

	/**
	 * @return	Huxtable\Core\File\Directory
	 */
	protected function getSourceFile()
	{
		$dirFig = new Core\File\Directory( Fig::DIR_FIG );
		$dirAssets = $dirFig
			->childDir( $this->appName )
			->childDir( Profile::ASSETS_DIRNAME )
			->childDir( $this->profileName );

		$pathSource = "{$dirAssets}/{$this->source}";
		$sourceFile = Core\File\File::getTypedInstance( $pathSource );

		return $sourceFile;
	}

	/**
	 * @return	void
	 */
	public function updateAssetsFromTarget()
	{
		if( empty( $this->source ) )
		{
			return false;
		}

		$sourceFile = $this->getSourceFile();
		if( $sourceFile->exists() )
		{
			$sourceFile->delete();
		}

		$this->target->copyTo( $sourceFile );
	}
}
