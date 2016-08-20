<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Fig\Action\Action;
use Huxtable\Core\File;

class App
{
	const COMMANDS_FILENAME = 'commands.yml';

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

		/* Profiles */
		$appDirChildren = $dirApp->children( $fileFilter );
		foreach( $appDirChildren as $dirProfile )
		{
			$fileConfig = $dirProfile->child( Profile::CONFIG_FILENAME );

			// Just ignore folders with no config file, don't make a fuss about it
			if( $fileConfig->exists() )
			{
				$profile = Profile::getInstanceFromDirectory( $dirProfile, $appName );
				$app->addProfile( $profile );
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

		$profile = $this->profiles[$profileName];
		while( !is_null( $profile->getParentName() ) )
		{
			$parentProfileName = $profile->getParentName();
			if( !isset( $this->profiles[$parentProfileName] ) )
			{
				throw new \OutOfRangeException( "Profile not found '{$this->name}/{$parentProfileName}'" );
			}

			$parentProfile = $this->profiles[$parentProfileName];
			$profile = $parentProfile->extendWith( $profile );
		}

		return $profile;
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
			$dirApp->create();
		}

		// Commands
		$commandsFile = $dirApp->child( self::COMMANDS_FILENAME );

		$commands = [];
		foreach( $this->commands as $command )
		{
			$commands[$command->getName()] = $command->getCommand();
		}
		$commandsYAML = Fig::encodeData( $commands );
		$commandsFile->putContents( $commandsYAML );

		// Profiles
		foreach( $this->profiles as $profileName => $profile )
		{
			$dirProfile = $dirApp->childDir( $profileName );
			$profile->write( $dirProfile );
		}
	}
}
