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
	public function __construct( File\Directory $dirFig )
	{
		$this->dirFig = $dirFig;
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
	 * @param	string	$profileName
	 * @return	void
	 */
	public function deployProfile( $appName, $profileName )
	{
		$app = $this->getApp( $appName );
		$profile = $app->getProfile( $profileName );

		$commands = $app->getCommands();
		$commandNames = $profile->getCommands();

		echo PHP_EOL;

		// Run pre-deployment commands
		foreach( $commandNames['pre'] as $preCommandName )
		{
			$output = ['OK'];
			$outputColor = 'green';

			if( isset( $commands[$preCommandName] ) )
			{
				$preCommand = $commands[$preCommandName];
				$preCommandResult = $preCommand->exec();

				/* Always */
				if( count( $preCommandResult['output'] ) > 0 && !$preCommand->ignoreErrors )
				{
					$output = $preCommandResult['output'];
				}

				/* Failure */
				if( $preCommandResult['exitCode'] != 0 )
				{
					if( !$preCommand->ignoreErrors )
					{
						$outputColor = 'red';
					}
				}
			}
			/* Command not found */
			else
			{
				$output = ['skipping: command not found'];
				$outputColor = 'yellow';
			}

			self::outputAction( 'POST', $preCommandName, $output, $outputColor );
		}

		// Deploy assets
		$assets = $profile->getAssets();
		foreach( $assets as $asset )
		{
			$asset->deploy();

			$category = strtoupper( $asset->getActionName() );
			$actionName = $asset->getName();

			self::outputAction( $category, $actionName, ['OK'], 'green' );
		}

		// Run post-deployment commands
		foreach( $commandNames['post'] as $postCommandName )
		{
			$output = ['OK'];
			$outputColor = 'green';

			if( isset( $commands[$postCommandName] ) )
			{
				$postCommand = $commands[$postCommandName];
				$postCommandResult = $postCommand->exec();

				/* Always */
				if( count( $postCommandResult['output'] ) > 0 && !$postCommand->ignoreErrors )
				{
					$output = $postCommandResult['output'];
				}

				/* Failure */
				if( $postCommandResult['exitCode'] != 0 )
				{
					if( !$postCommand->ignoreErrors )
					{
						$outputColor = 'red';
					}
				}
			}
			/* Command not found */
			else
			{
				$output = ['skipping: command not found'];
				$outputColor = 'yellow';
			}

			self::outputAction( 'POST', $postCommandName, $output, $outputColor );
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
		self::outputAction( 'RUN', "{$appName}:{$commandName}", $output, $outputColor );
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
				throw new \OutOfRangeException( "App not found '{$appName}'" );
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
	 * @param	string	$actionName
	 * @param	array	$output
	 * @param	string	$outputColor
	 * @return	void
	 */
	public function outputAction( $category, $actionName, array $output, $outputColor )
	{
		echo sprintf( "%'*-80s", "{$category} [ {$actionName} ] " ) . PHP_EOL;

		$outputString = new Format\String();
		$outputString->foregroundColor( $outputColor );

		foreach( $output as $line )
		{
			$outputString->setString( $line );
			echo $outputString . PHP_EOL;
		}

		echo PHP_EOL;
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
}
