<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Cranberry\Core\File as CoreFile;
use Fig;

class File extends Action
{
	const SKIP           = 0;
	const CREATE         = 1;
	const REPLACE        = 2;
	const DELETE         = 4;
	const REPLACE_STRING = 8;

	const STRING_INVALID_PATH = 'Invalid path: %s';

	/**
	 * Valid actions
	 *
	 * @var	array
	 */
	protected $actions = ['create', 'delete', 'replace', 'replace_string'];

	/**
	 * @var	int
	 */
	protected $action = self::SKIP;

	/**
	 * @var	string
	 */
	protected $actionName;

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
	 * @var	boolean
	 */
	protected $propertySourceIsRequired=false;

	/**
	 * @var	boolean
	 */
	protected $propertyStringIsRequired=false;

	/**
	 * @var	string
	 */
	protected $replacementStringNew='';

	/**
	 * @var	string
	 */
	protected $replacementStringOld='';

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
		/*
		 * Validate 'file' definition before continuing.
		 *
		 * Since 'file' is not stored as a property of the Defaults object, we
		 * don't use `defineProperty` or `setPropertyValues` for validation.
		 */
		if( !isset( $properties['file'] ) )
		{
			$missingPropertyMessage = sprintf( \Fig\Fig::STRING_MISSING_REQUIRED_PROPERTY, 'file' );
			throw new \InvalidArgumentException( $missingPropertyMessage );
		}

		if( !is_array( $properties['file'] ) )
		{
			$stringValue = \Fig\Fig::getStringRepresentation( $properties['file'] );
			$invalidPropertyMessage = sprintf( \Fig\Fig::STRING_INVALID_PROPERTY_VALUE, 'file', $stringValue );

			throw new \InvalidArgumentException( $invalidPropertyMessage );
		}

		/*
		 * Flatten 'file' properties with top-level properties, then validate
		 */
		$fileProperties = array_merge( $properties, $properties['file']);
		unset( $fileProperties['file'] );

		/* 'action' */
		$this->defineProperty( 'action', true, function( $value )
		{
			if( !is_string( $value ) )
			{
				return false;
			}

			return in_array( strtolower( $value ), $this->actions );

		}, array( $this, 'setAction' ));

		/* 'path' */
		$this->defineProperty( 'path', true, 'self::isStringish', function( $value )
		{
			$this->target = CoreFile\File::getTypedInstance( $value );
		});

		/* 'contents' */
		$this->defineProperty( 'contents', false, function( $value )
		{
			/* `file_put_contents` accepts strings or arrays */
			return self::isStringish( $value ) || is_array( $value );
		});

		/* 'source' */
		$this->defineProperty( 'source', function()
		{
			/* This property is only required for `file:replace` actions */
			return $this->propertySourceIsRequired;

		}, 'is_string' );

		/* 'string' */
		$this->defineProperty( 'string', function()
		{
			/* This property is only required for `file:replace_string` actions */
			return $this->propertyStringIsRequired;
		},
		function( $value )
		{
			/* 'string' must conform to ['old'=>~string, 'new'=>~string] */
			if( !is_array( $value ) )
			{
				return false;
			}
			if( !array_key_exists( 'old', $value ) || !array_key_exists( 'new', $value ) )
			{
				return false;
			}
			if( !self::isStringish( $value['old'] ) || !self::isStringish( $value['new'] ) )
			{
				return false;
			}

			return true;
		},
		function( $value )
		{
			$this->replacementStringOld = $value['old'];
			$this->replacementStringNew = $value['new'];
		});

		parent::__construct( $fileProperties );
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

		/* Replace String */
		if( $this->action == self::REPLACE_STRING )
		{
			/* Validate $target */
			if( !$target->exists() )
			{
				$invalidPropertyMessage = sprintf( \Fig\Fig::STRING_INVALID_PROPERTY_VALUE, 'path', "No such file '{$target}'" );
				throw new \InvalidArgumentException( $invalidPropertyMessage );
			}

			if( $target->isDir() )
			{
				$invalidPropertyMessage = sprintf( \Fig\Fig::STRING_INVALID_PROPERTY_VALUE, 'path', "'{$target}' is a directory" );
				throw new \InvalidArgumentException( $invalidPropertyMessage );
			}

			if( !is_readable( $target ) )
			{
				$invalidPropertyMessage = sprintf( \Fig\Fig::STRING_INVALID_PROPERTY_VALUE, 'path', "Cannot read from '{$target}': Permission denied" );
				throw new \InvalidArgumentException( $invalidPropertyMessage );
			}

			if( !is_writeable( $target ) )
			{
				$invalidPropertyMessage = sprintf( \Fig\Fig::STRING_INVALID_PROPERTY_VALUE, 'path', "Cannot write to '{$target}': Permission denied" );
				throw new \InvalidArgumentException( $invalidPropertyMessage );
			}

			/* Perform replacement */
			$oldContents = $target->getContents();

			$oldString = Fig\Fig::replaceVariables( $this->replacementStringOld, $this->variables );
			$newString = Fig\Fig::replaceVariables( $this->replacementStringNew, $this->variables );

			$oldPattern = "/{$oldString}/";
			$newContents = preg_replace( $oldPattern, $newString, $oldContents );

			$target->putContents( $newContents );
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
	 * Get integer representation of action
	 *
	 * @return	int
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Get string representation of action
	 *
	 * @return	string
	 */
	public function getActionName()
	{
		return $this->actionName;
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
	 * @param	string	$actionName
	 * @return	void
	 */
	public function setAction( $actionName )
	{
		$actionName = strtolower( $actionName );

		switch( $actionName )
		{
			case 'create':
				$this->action = self::CREATE;
				$this->actionName = 'create';
				break;

			case 'delete':
				$this->action = self::DELETE;
				$this->actionName = 'delete';
				break;

			case 'replace':
				$this->action = self::REPLACE;
				$this->actionName = 'replace';

				$this->propertySourceIsRequired = true;
				break;

			case 'replace_string':
				$this->action = self::REPLACE_STRING;
				$this->actionName = 'replace_string';

				$this->propertyStringIsRequired = true;
				break;
		}
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

		/* Replace variables */
		$targetPathname = $this->target->getPathname();
		$targetPathname = Fig\Fig::replaceVariables( $targetPathname, $this->variables );
		$target = CoreFile\File::getTypedInstance( $targetPathname );

		if( $target->exists() )
		{
			$target->copyTo( $sourceFile );
		}

		return true;
	}
}
