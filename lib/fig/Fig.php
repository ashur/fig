<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\CLI\Format;
use Huxtable\Core\File;
use Spyc;

class Fig
{
	const DIR_FIG = '~/.fig';

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
	protected $dirFig;

	/**
	 * @return	void
	 */
	public function __construct()
	{
		$this->dirFig = new File\Directory( self::DIR_FIG );
		if( !$this->dirFig->exists() )
		{
			$this->dirFig->create();
		}

		// Only include directories
		$fileFilter = new File\Filter();
		$fileFilter->setDefaultMethod( File\Filter::METHOD_INCLUDE );
		$fileFilter->addInclusionRule( function( $file )
		{
			return $file->isDir();
		});

		$apps = $this->dirFig->children( $fileFilter );

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
	 * @param	string	$appName
	 * @return	Fig\App
	 */
	public function createApp( $appName )
	{
		$appDir = $this->dirFig->childDir( $appName );

		if( $appDir->exists() )
		{
			throw new \Exception( "App '{$appName}' already exists. See 'fig show'." );
		}

		/* Create the directory on disk */
		$appDir->create();

		/* Update the internal inventory, just in case */
		$this->appDirs[$appName] = $appDir;
		ksort( $this->appDirs );

		$app = $this->getApp( $appName );
		return $app;
	}

	/**
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @return	void
	 */
	public function createProfile( $appName, $profileName )
	{
		if( !isset( $this->appDirs[$appName] ) )
		{
			throw new \OutOfRangeException( "App not found '{$appName}'" );
		}

		$appDir = $this->appDirs[$appName];
		$profileFile = $appDir->child( "{$profileName}.yml" );

		if( $profileFile->exists() )
		{
			throw new \Exception( "Profile '{$appName}/{$profileName}' already exists. See 'fig show'." );
		}

		$profileContents = <<<PROFILE
# {$profileName}
---

# A command example
- name: echo
	command: echo 'Hello, world'.

# A file example
- name: hello-world.txt
	file:
		skip: ~/Desktop/hello-world.txt
		# create: ~/Desktop/hello-world.txt
		# delete: ~/Desktop/hello-world.txt

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
		$data = Spyc::YAMLLoad( $file );
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
		/* Delete the source directory */
		$appDir = $this->appDirs[$appName];
		$appDir->delete();

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
		/* Delete the source file */
		$appDir = $this->appDirs[$appName];
		$profileFile = $appDir->child( "{$profileName}.yml" );
		$profileFile->delete();

		/* Update the internal inventory */
		if( isset( $this->apps[$appName] ) )
		{
			$this->apps[$appName]->removeProfile( $profileName );
		}
	}

	/**
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @return	void
	 */
	public function deployProfile( $appName, $profileName )
	{
		$app = $this->getApp( $appName );
		$profile = $app->getProfile( $profileName );

		/*
		 * Actions
		 */
		$actions = $profile->getActions();
		foreach( $actions as $action )
		{
			$outputColor = 'green';

			try
			{
				$result = $action->execute();
			}
			catch( \Exception $e )
			{
				$result['title'] = $action->name;
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
				self::outputAction( $action->type, $result['title'], $output, $outputColor );
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
		/* Verify required keys are set */
		self::validateRequiredKeys( $data, ['name'] );

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
	 * @param	string	$category
	 * @param	string	$actionTitle
	 * @param	mixed	$output
	 * @param	string	$outputColor
	 * @return	void
	 */
	public function outputAction( $category, $title, $output, $outputColor )
	{
		echo PHP_EOL;

		$category = strtoupper( $category );
		echo sprintf( "%'*-80s", "{$category}: {$title} " ) . PHP_EOL;

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
	 * Update a profile's source files using their targets
	 *
	 * @param	string	$appName
	 * @param	string	$profileName
	 * @return	void
	 */
	public function updateProfileAssetsFromTarget( $appName, $profileName )
	{
		$app = $this->getApp( $appName );
		$profile = $app->getProfile( $profileName );

		$profile->updateAssetsFromTarget();
	}

	/**
	 * @param	array	$array
	 * @param	arra	$requiredKeys
	 * @return	void
	 */
	static public function validateRequiredKeys( array $array, array $requiredKeys )
	{
		foreach( $requiredKeys as $requiredKey )
		{
			if( !isset( $array[$requiredKey] ) )
			{
				throw new \Exception( "Missing required key '{$requiredKey}'." );
			}
		}
	}
}
