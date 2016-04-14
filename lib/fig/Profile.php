<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\Core\File;

class Profile
{
	const CONFIG_FILENAME = 'config.json';

	/**
	 * @var	array
	 */
	protected $commands;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @var	string
	 */
	protected $parentName;

	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function __construct( $name )
	{
		$this->name = $name;

		$this->commands['pre'] = [];
		$this->commands['post'] = [];
	}

	/**
	 * @param	string	$command
	 * @return	self
	 */
	public function addPostCommand( $command )
	{
		$this->commands['post'][] = $command;
		return $this;
	}

	/**
	 * @param	string	$command
	 * @return	self
	 */
	public function addPreCommand( $command )
	{
		$this->commands['pre'][] = $command;
		return $this;
	}

	/**
	 * @param	Fig\Profile		$profile
	 * @return	Fig\Profile
	 */
	public function extendWith( Profile $profile )
	{
		$extendedProfile = $this;

		// Commands
		$commands = $profile->getCommands();
		foreach( $commands['pre'] as $preCommand )
		{
			$extendedProfile->addPreCommand( $preCommand );
		}
		foreach( $commands['post'] as $postCommand )
		{
			$extendedProfile->addPostCommand( $postCommand );
		}

		return $extendedProfile;
	}

	/**
	* @return	array
	*/
	public function getCommands()
	{
		return $this->commands;
	}

	/**
	 * @param	Huxtable\Core\File\Directory	$dirProfile
	 * @return	Fig\Profile
	 */
	static public function getInstanceFromDirectory( File\Directory $dirProfile )
	{
		$profile = new self( $dirProfile->getBasename() );

		// Populate fields from config file
		$configFile = $dirProfile->child( self::CONFIG_FILENAME );

		if( !$configFile->exists() )
		{
			// ...
		}

		$config = json_decode( $configFile->getContents(), true );
		if( json_last_error() != JSON_ERROR_NONE )
		{
			throw new \Exception( "Malformed profile configuration: " . json_last_error_msg() );
		}

		// Profile extends parent profile
		if( isset( $config['extends'] ) )
		{
			$profile->setParentName( $config['extends'] );
		}

		// Commands
		if( isset( $config['commands']['pre'] ) )
		{
			foreach( $config['commands']['pre'] as $preCommand )
			{
				$profile->addPreCommand( $preCommand );
			}
		}
		if( isset( $config['commands']['post'] ) )
		{
			foreach( $config['commands']['post'] as $postCommand )
			{
				$profile->addPostCommand( $postCommand );
			}
		}

		return $profile;
	}

	/**
	* @return	string
	*/
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return	string
	 */
	public function getParentName()
	{
		return $this->parentName;
	}

	/**
	 * @param	string	$parentName
	 * @return	void
	 */
	public function setParentName( $parentName )
	{
		$this->parentName = $parentName;
	}

	/**
	 * Write contents of profile to disk
	 *
	 * @param	Huxtable\Core\File\Directory	$dirProfile
	 * @return	void
	 */
	public function write( $dirProfile )
	{
		$configData = [];
		$configFile = $dirProfile->child( self::CONFIG_FILENAME );

		if( !$dirProfile->exists() )
		{
			$dirProfile->mkdir( 0777, true );
		}

		// Populate configuration contents
		$configData['commands'] = $this->commands;

		if( isset( $this->parentName ) )
		{
			$configData['extends'] = $this->parentName;
		}

		$configContents = json_encode( $configData );
		$configFile->putContents( $configContents );
	}
}
