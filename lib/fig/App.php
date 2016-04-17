<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\Core\File;

class App
{
	const COMMANDS_FILENAME = 'commands.json';

	/**
	 * @var	array
	 */
	protected $commands=[];

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @var	array
	 */
	protected $profiles=[];

	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function __construct( $name )
	{
		$this->name = $name;
	}

	/**
	 * @param	Fig\Command		$command
	 * @return	self
	 */
	public function addCommand( Command $command )
	{
		$commandName = $command->getName();
		$this->commands[$commandName] = $command;

		return $this;
	}

	/**
	 * @param	Fig\Profile		$profile
	 * @return	self
	 */
	public function addProfile( Profile $profile )
	{
		$profileName = $profile->getName();
		$this->profiles[$profileName] = $profile;

		return $this;
	}

	/**
	 * @param	string		$commandName
	 * @return	Fig\Command
	 */
	public function getCommand( $commandName )
	{
		if( !isset( $this->commands[$commandName] ) )
		{
			throw new \OutOfRangeException( "Command not found '{$this->name}:{$commandName}'" );
		}

		return $this->commands[$commandName];
	}

	/**
	 * @param	Huxtable\Core\File\Directory	$dirApp
	 * @return	Fig\App
	 */
	static public function getInstanceFromDirectory( File\Directory $dirApp )
	{
		$app = new self( $dirApp->getBasename() );

		// Only include visible folders
		$fileFilter = new File\Filter();
		$fileFilter->setDefaultMethod( File\Filter::METHOD_INCLUDE );
		$fileFilter->addInclusionRule( function( $file )
		{
			$include = true;
			$include = $file->isDir() && $include;
			$include =  (substr( $file->getFilename(), 0, 1 ) != '.') && $include;

			return $include;
		});

		// Profiles
		$appDirChildren = $dirApp->children( $fileFilter );
		foreach( $appDirChildren as $dirProfile )
		{
			$profile = Profile::getInstanceFromDirectory( $dirProfile );
			$app->addProfile( $profile );
		}

		// Commands
		$commandsFile = $dirApp->child( self::COMMANDS_FILENAME );

		if( $commandsFile->exists() )
		{
			$commandsContents = $commandsFile->getContents();
			$commandsData = json_decode( $commandsContents, true );

			foreach( $commandsData as $commandName => $commandString )
			{
				$command = new Command( $commandName, $commandString );
				$app->addCommand( $command );
			}
		}

		return $app;
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param	string	$profileName
	 * @return	Fig\Profile
	 */
	public function getProfile( $profileName )
	{
		if( !isset( $this->profiles[$profileName] ) )
		{
			throw new \OutOfRangeException( "Profile not found '{$this->name}/{$profileName}'" );
		}

		return $this->profiles[$profileName];
	}

	/**
	 * @return	array
	 */
	public function getProfiles()
	{
		return $this->profiles;
	}

	/**
	 * @param	Huxtable\Core\File\Directory	$dirApp
	 * @return	void
	 */
	public function write( File\Directory $dirApp )
	{
		if( !$dirApp->exists() )
		{
			$dirApp->mkdir( 0777, true );
		}

		// Commands
		$commandsFile = $dirApp->child( self::COMMANDS_FILENAME );

		$commands = [];
		foreach( $this->commands as $command )
		{
			$commands[$command->getName()] = $command->getCommand();
		}
		$commandsJSON = json_encode( $commands );
		$commandsFile->putContents( $commandsJSON );

		// Profiles
		foreach( $this->profiles as $profileName => $profile )
		{
			$dirProfile = $dirApp->childDir( $profileName );
			$profile->write( $dirProfile );
		}
	}
}
