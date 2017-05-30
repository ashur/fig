<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\CLI\Format;
use Cranberry\CLI\Shell;
use Cranberry\Core\File;
use Spyc;

class Fig
{
	const STRING_INVALID_PROPERTY_VALUE = "Invalid value for property '%s': %s";
	const STRING_MISSING_REQUIRED_PROPERTY = "Missing required property '%s'";

	/**
	 * @var	array
	 */
	protected $appDirs=[];

	/**
	 * @var	array
	 */
	protected $apps=[];

	/**
	 * @var	Huxtable\Core\File\Directory
	 */
	protected $figDirectory;

	/**
	 * @return	void
	 */
	public function __construct( File\Directory $figDirectory )
	{
		$this->figDirectory = $figDirectory;

		if( !$this->figDirectory->exists() )
		{
			$this->figDirectory->create();
		}

		// Only include directories
		$fileFilter = new File\Filter();
		$fileFilter->setDefaultMethod( File\Filter::METHOD_INCLUDE );
		$fileFilter->addInclusionRule( function( $file )
		{
			return $file->isDir();
		});

		$apps = $this->figDirectory->children( $fileFilter );

		foreach( $apps as $appDir )
		{
			// Add Directory object for later instantiation
			$appName = $appDir->getBasename();
			$this->appDirs[$appName] = $appDir;
		}
	}

	/**
	 * @param	Fig\App		$app
	 * @return	self
	 */
	public function addApp( App $app )
	{
		$this->apps[$app->getName()] = $app;
		return $this;
	}

	/**
	 * Create an template app folder
	 *
	 * @param	string	$newAppName
	 * @return	Fig\App
	 */
	public function createApp( $newAppName )
	{
		/* Create new instance to validate $newAppName value */
		$app = new App( $newAppName );
		$appName = $app->getName();

		$appDir = $this->figDirectory->childDir( $appName );

		if( $appDir->exists() )
		{
			throw new \Exception( "App '{$appName}' already exists. See 'fig show'." );
		}

		/* Create directories on disk */
		$appDir->create();

		$assetsDir = $appDir->childDir( Profile::ASSETS_DIRNAME );
		$assetsDir->create();

		/* Update the internal inventory, just in case */
		$this->appDirs[$appName] = $appDir;
		ksort( $this->appDirs );

		$app = $this->getApp( $appName );
		return $app;
	}

	/**
	 * @param	string	$appName
	 * @param	string	$repoURL
	 * @return	void
	 */
	public function createAppFromRepository( $appName, $repoURL )
	{
		$appDir = $this->figDirectory->childDir( $appName );
		$commandClone = sprintf( 'git clone %s %s', $repoURL, $appDir );

		echo 'Cloning... ';
		$result = Shell::exec( $commandClone, true, '   > ' );

		if( $result['exitCode'] == 0 )
		{
			echo 'done.' . PHP_EOL;
			return true;
		}

		echo 'failed:' . PHP_EOL . PHP_EOL;
		echo $result['output']['formatted'] . PHP_EOL;
		return false;
	}

	/**
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @param	string	$actionContents
	 * @return	void
	 */
	public function createProfile( $appName, $profileName, $actionContents )
	{
		if( !isset( $this->appDirs[$appName] ) )
		{
			throw new \OutOfRangeException( "App not found '{$appName}'" );
		}

		$appDir = $this->appDirs[$appName];

		/* Create the assets directory */
		$assetsDir = $appDir
			->childDir( Profile::ASSETS_DIRNAME )
			->childDir( $profileName );
		$assetsDir->create();

		$profileFile = $appDir->child( "{$profileName}.yml" );
		if( $profileFile->exists() )
		{
			throw new \Exception( "Profile '{$appName}/{$profileName}' already exists. See 'fig show'." );
		}

		$profileContents = <<<PROFILE
# {$profileName}
---

{$actionContents}

PROFILE;

		/* Update the internal inventory */
		$profileFile->putContents( $profileContents );

		/* Create a stub object */
		if( isset( $this->apps[$appName] ) )
		{
			$profile = new Profile( $profileName, $appName );
			$this->apps[$appName]->addProfile( $profile );
		}
	}

	/**
	 * Decode data with a consistent format (currently YAML)
	 *
	 * @param	Huxtable\Core\File\File	$file
	 * @return	array
	 */
	static public function decodeFile( File\File $file )
	{
		$fileContents = $file->getContents();
		$data = Spyc::YAMLLoadString( $fileContents );
		return $data;
	}

	/**
	 * Decode data with a consistent format (currently YAML)
	 *
	 * @param	string	$string
	 * @return	array
	 */
	static public function decodeString( $string )
	{
		$data = Spyc::YAMLLoadString( $string );
		return $data;
	}

	/**
	 * @param	string	$appName
	 * @return	void
	 */
	public function deleteApp( $appName )
	{
		$appDir = $this->appDirs[$appName];

		/*
		 * Delete the source directory
		 *
		 * Note: This uses a shell command instead of the Directory::delete method
		 *       because `-f` is not available via PHP filesystem functions.
		 *       Since apps are likely under version control, `-f` is required
		 *       to remove the app directory without user intervention.
		 */
		$command = "rm -rf '{$appDir}'";
		$result = Shell::exec( $command );
		if( $result['exitCode'] !== 0 )
		{
			throw new \Exception( $result['output']['raw'], $result['exitCode'] );
		}

		/* Update the internal inventory */
		unset( $this->appDirs[$appName] );

		if( isset( $this->apps[$appName] ) )
		{
			unset( $this->apps[$appName] );
		}
	}

	/**
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @return	void
	 */
	public function deleteProfile( $appName, $profileName )
	{
		$appDir = $this->appDirs[$appName];

		/* Delete the assets directory */
		$assetsDir = $appDir
			->childDir( Profile::ASSETS_DIRNAME )
			->childDir( $profileName );

		if( $assetsDir->exists() )
		{
			$assetsDir->delete();
		}

		/* Delete the source file */
		$profileFile = $appDir->child( "{$profileName}.yml" );

		if( $profileFile->exists() )
		{
			$profileFile->delete();
		}

		/* Update the internal inventory */
		if( isset( $this->apps[$appName] ) )
		{
			$this->apps[$appName]->removeProfile( $profileName );
		}
	}

	/**
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @param	array	$variables
	 * @return	void
	 */
	public function deployProfile( $appName, $profileName, array $variables=null )
	{
		$app = $this->getApp( $appName );

		/*
		 * Actions
		 */
		$actions = $app->getProfileActions( $profileName );
		foreach( $actions as $action )
		{
			/* Variables */
			if( !is_null( $variables ) )
			{
				$action->setVariables( $variables );
			}

			$outputColor = 'green';
			$actionTitle = $action->getTitle();

			/* Set Fig directory for 'file' actions */
			if( $action->usesFigDirectory )
			{
				$action->setFigDirectory( $this->figDirectory );
			}

			self::outputActionTitle( $action->type, $actionTitle );

			try
			{
				$result = $action->execute();
			}
			catch( \Exception $e )
			{
				$result['error'] = true;
				$result['output'] = $e->getMessage();
			}
			$output = empty( $result['output'] ) ? 'OK' : $result['output'];

			if( $result['error']  )
			{
				$outputColor = 'red';
			}

			if( !isset( $result['silent'] ) || !$result['silent'] )
			{
				self::outputAction( $output, $outputColor );
			}

			if( $action->usesDeprecatedSyntax )
			{
				$deprecationWarning = new Format\String( 'WARNING: This action uses a deprecated syntax. See https://github.com/ashur/fig/wiki/Actions' );
				$deprecationWarning->foregroundColor( 'yellow' );

				echo $deprecationWarning . PHP_EOL;
			}
		}
	}

	/**
	 * Encode data with a consistent format (currently YAML)
	 *
	 * @param	array	$data
	 * @return	string
	 */
	static public function encodeData( array $data )
	{
		$encoded = Spyc::YAMLDump( $data, 4, 0 );
		return $encoded;
	}

	/**
	 * @param	string	$appName
	 * @param	string	$commandName
	 * @return	void
	 * @todo	Gracefully handle command returning exit code
	 */
	public function executeCommand( $appName, $commandName )
	{
		$app = $this->getApp( $appName );

		$command = $app->getCommand( $commandName );
		$commandResult = $command->exec();

		$output = ['OK'];
		$outputColor = 'green';

		/* Always */
		if( count( $commandResult['output'] ) > 0 && !$command->ignoreErrors )
		{
			$output = $commandResult['output'];
		}

		/* Failure */
		if( $commandResult['exitCode'] != 0 )
		{
			if( !$command->ignoreErrors )
			{
				$outputColor = 'red';
			}
		}

		echo PHP_EOL;
		self::outputAction( 'Run', "{$appName}:{$commandName}", $output, $outputColor );
	}

	/**
	 * This needs to live in Fig since Action\Action is abstract
	 *
	 * @param	array	$data
	 * @return	Fig\Action\Action
	 */
	static public function getActionInstanceFromData( array $data )
	{
		/* Get instance of Action class */
		$actionClasses['command']	= 'Command';
		$actionClasses['defaults']	= 'Defaults';
		$actionClasses['file']		= 'File';

		foreach( $actionClasses as $dataKey => $actionClass )
		{
			if( isset( $data[$dataKey] ) )
			{
				$className = "Fig\Action\\{$actionClass}";
				$action = new $className( $data );
				return $action;
			}
		}

		throw new \Exception( 'Unknown action' );
	}

	/**
	 * @param	string	$appName
	 * @return	Fig\App
	 */
	public function getApp( $appName )
	{
		if( !isset( $this->apps[$appName] ) )
		{
			if( !isset( $this->appDirs[$appName] ) )
			{
				throw new \OutOfRangeException( "App '{$appName}' not found." );
			}

			// Lazy load the app
			$app = App::getInstanceFromDirectory( $this->appDirs[$appName] );
			$this->addApp( $app );
		}

		return $this->apps[$appName];
	}

	/**
	 * @return	array
	 */
	public function getApps()
	{
		$apps = $this->apps;

		foreach( $this->appDirs as $appName => $appDir )
		{
			if( !isset( $apps[$appName] ) )
			{
				$apps[$appName] = App::getInstanceFromDirectory( $appDir );
			}
		}

		return array_values( $apps );
	}

	/**
	 * @return	Cranberry\Core\File\Directory
	 */
	public function getFigDirectory()
	{
		return $this->figDirectory;
	}

	/**
	 * @param	string	$string
	 * @param	array	$variables
	 * @return	void
	 */
	static public function replaceVariables( $string, array $variables )
	{
		foreach( $variables as $key => $value )
		{
			$string = str_replace( "{{{$key}}}", $value, $string );
			$string = str_replace( "{{ {$key} }}", $value, $string );
		}

		return $string;
	}

	/**
	 * @param	mixed	$output
	 * @param	string	$outputColor
	 * @return	void
	 */
	public function outputAction( $output, $outputColor )
	{
		$outputString = new Format\String();
		$outputString->foregroundColor( $outputColor );

		if( is_scalar( $output ) )
		{
			$output = [$output];
		}

		foreach( $output as $line )
		{
			if( $line === false )
			{
				$line = 'false';
			}
			if( $line === true )
			{
				$line = 'true';
			}

			$outputString->setString( $line );
			echo $outputString . PHP_EOL;
		}
	}

	/**
	 * @param	string	$category
	 * @param	string	$actionTitle
	 */
	public function outputActionTitle( $category, $title )
	{
		if( is_null( $title ) )
		{
			return;
		}

		echo PHP_EOL;

		$category = strtoupper( $category );
		echo sprintf( "%'*-80s", "{$category}: {$title} " ) . PHP_EOL;
	}

	/**
	 * Update a profile's source files using their targets
	 *
	 * @param	Fig\Profile	$profile
	 */
	public function updateProfileAssetsFromTarget( Profile $profile )
	{
		$profile->updateAssetsFromTarget( $this->figDirectory );
	}
}
