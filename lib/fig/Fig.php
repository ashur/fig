<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\Core\File;

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
			$this->dirFig->mkdir( 0777, true );
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

		// Run pre-deployment commands
		foreach( $commandNames['pre'] as $preCommandName )
		{
			if( isset( $commands[$preCommandName] ) )
			{
				$preCommand = $commands[$preCommandName];
				$result = $preCommand->exec();

				// Command returned an error...
				if( $result['exitCode'] != 0 )
				{
					throw new \Exception( "Deployment halted: command '{$preCommandName}' returned an error" );
				}
			}
		}

		// Deploy assets
		$assets = $profile->getAssets();
		foreach( $assets as $asset )
		{
			$asset->deploy();
		}

		// Run post-deployment commands
		foreach( $commandNames['post'] as $postCommandName )
		{
			if( isset( $commands[$postCommandName] ) )
			{
				$postCommand = $commands[$postCommandName];
				$result = $postCommand->exec();

				// Command returned an error...
				if( $result['exitCode'] != 0 )
				{
					throw new \Exception( "Deployment halted: command '{$preCommandName}' returned an error" );
				}
			}
		}
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

		$result = $command->exec();

		// Command returned an error...
		if( $result['exitCode'] != 0 )
		{
			// ...
		}
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
