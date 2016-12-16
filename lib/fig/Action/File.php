<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig;
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
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		parent::__construct( $properties );

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
			Fig\Fig::validateRequiredKeys( $properties['file'], ['source'] );

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

		/* Human-readable action label */
		switch( $this->action )
		{
			case self::CREATE:
				$this->actionName = 'create';
				break;

			case self::REPLACE:
				$this->actionName = 'replace';
				break;

			case self::DELETE:
				$this->actionName = 'delete';
				break;

			case self::SKIP:
			default:
				$this->actionName = 'skip';
				break;
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

		/* Replace variables */
		$targetPathname = $this->target->getPathname();
		$targetPathname = Fig\Fig::replaceVariables( $targetPathname, $this->variables );
		$target = Core\File\File::getTypedInstance( $targetPathname );

		/* Create */
		if( $this->action == self::CREATE )
		{
			$didSucceed = $didSucceed && $target->create();

			if( !empty( $this->contents ) )
			{
				$contents = Fig\Fig::replaceVariables( $this->contents, $this->variables );
				$didSucceed = $didSucceed && $target->putContents( $contents );
			}
		}

		/* Replace */
		if( $this->action == self::REPLACE )
		{
			$sourceFile = $this->getSourceFile();	// File or Directory

			if( !$sourceFile->exists() )
			{
				throw new \Exception( "Source missing: '{$sourceFile}'" );
			}

			if( $target->exists() )
			{
				$target->delete();
			}

			$didSucceed = $didSucceed && $sourceFile->copyTo( $target );
		}

		/* Delete */
		if( $this->action == self::DELETE )
		{
			$target->delete();
		}

		/* Results */
		$result['title'] = $this->getTitle();
		$result['error'] = !$didSucceed;
		$result['output'] = null;

		/* Modify Output */
		if( $this->ignoreOutput )
		{
			$result['output'] = null;
		}
		if( $this->ignoreErrors && $result['error'] == true )
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
		$dirFig = new Core\File\Directory( Fig\Fig::DIR_FIG );
		$dirAssets = $dirFig
			->childDir( $this->appName )
			->childDir( Fig\Profile::ASSETS_DIRNAME )
			->childDir( $this->profileName );

		$pathSource = "{$dirAssets}/{$this->source}";
		$sourceFile = Core\File\File::getTypedInstance( $pathSource );

		return $sourceFile;
	}

	/**
	 * @return	string
	 */
	public function getTitle()
	{
		$title = "{$this->actionName} | {$this->name}";
		$title = Fig\Fig::replaceVariables( $title, $this->variables );

		return $title;
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

		if( $this->target->exists() )
		{
			$this->target->copyTo( $sourceFile );
		}
	}
}
