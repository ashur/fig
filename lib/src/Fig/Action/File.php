<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Cranberry\Core\File as CoreFile;
use Fig;

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
	 * @var	Cranberry\Core\File\Directory
	 */
	protected $figDirectory;

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $target;

	/**
	 * @var	string
	 */
	public $type = 'File';

	/**
	 * @var	boolean
	 */
	public $usesFigDirectory = true;

	/**
	 * @param	array	$properties
	 */
	public function __construct( array $properties )
	{
		parent::__construct( $properties );

		if( isset( $properties['file']['action'] ) )
		{
			Fig\Fig::validateRequiredKeys( $properties['file'], ['action','path'] );

			/* Action */
			$this->actionName = strtolower( $properties['file']['action'] );

			switch( $this->actionName )
			{
				case 'create':
					$this->action = self::CREATE;

					if( isset( $properties['file']['contents'] ) )
					{
						$this->contents = $properties['file']['contents'];
					}

					break;

				case 'delete':
					$this->action = self::DELETE;

					break;

				case 'replace':
					Fig\Fig::validateRequiredKeys( $properties['file'], ['source'] );

					$this->action = self::REPLACE;
					$this->source = $properties['file']['source'];

					break;

				default:
					throw new \Exception( "Unsupported file action '{$this->actionName}'." );
					break;
			}

			$this->target = CoreFile\File::getTypedInstance( $properties['file']['path'] );
		}

		/*
		 * Deprecated
		 */
		else
		{
			$this->usesDeprecatedSyntax = true;

			/* Create */
			if( isset( $properties['file']['create'] ) )
			{
				$this->action = self::CREATE;
				$this->target = CoreFile\File::getTypedInstance( $properties['file']['create'] );

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
				$this->target = CoreFile\File::getTypedInstance( $properties['file']['replace'] );
				$this->source = $properties['file']['source'];
			}

			/* Delete */
			if( isset( $properties['file']['delete'] ) )
			{
				$this->action = self::DELETE;
				$this->target = CoreFile\File::getTypedInstance( $properties['file']['delete'] );
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
		$target = CoreFile\File::getTypedInstance( $targetPathname );

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
		$assetsDirectory = $this->figDirectory
			->childDir( $this->appName )
			->childDir( Fig\Profile::ASSETS_DIRNAME )
			->childDir( $this->profileName );

		$pathSource = "{$assetsDirectory}/{$this->source}";
		$pathSource = Fig\Fig::replaceVariables( $pathSource, $this->variables );

		$sourceFile = CoreFile\File::getTypedInstance( $pathSource );

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
	 * @param	Cranberry\Core\File\Directory	$figDirectory
	 */
	public function setFigDirectory( CoreFile\Directory $figDirectory )
	{
		$this->figDirectory = $figDirectory;
	}

	/**
	 * Perform a reverse deployment
	 *
	 * @return	boolean
	 */
	public function updateAssetsFromTarget()
	{
		if( empty( $this->source ) )
		{
			return false;
		}

		/* The Fig asset located in assets/<profile> */
		$sourceFile = $this->getSourceFile();

		/* Ensure that assets/source folders exist prior to attempting update */
		if( !$sourceFile->parent()->exists() )
		{
			$sourceFile->parent()->create();
		}

		/* Perform the update */
		if( $sourceFile->exists() )
		{
			$sourceFile->delete();
		}

		if( $this->target->exists() )
		{
			$this->target->copyTo( $sourceFile );
		}

		return true;
	}
}
