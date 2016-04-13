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
	protected $parent;

	/**
	 * @var	Huxtable\Core\File\Directory
	 */
	protected $source;

	/**
	 * @param	Huxtable\Core\File\Directory	$source
	 * @return	void
	 */
	public function __construct( File\Directory $source )
	{
		$this->source = $source;
		$this->name = $source->getBasename();

		$this->commands['pre'] = [];
		$this->commands['post'] = [];

		// Populate fields from config file
		$configFile = $this->source->child( self::CONFIG_FILENAME );
		if( $configFile->exists() )
		{
			$config = json_decode( $configFile->getContents(), true );
			if( json_last_error() != JSON_ERROR_NONE )
			{
				throw new \Exception( "Malformed profile configuration: " . json_last_error_msg() );
			}

			// Profile extends parent profile
			if( isset( $config['extends'] ) )
			{
				$this->parent = $config['extends'];
			}

			// Commands
			if( isset( $config['commands']['pre'] ) )
			{
				$this->commands['pre'] = $config['commands']['pre'];
			}
			if( isset( $config['commands']['post'] ) )
			{
				$this->commands['post'] = $config['commands']['post'];
			}
		}
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
	 * @return	string
	 */
	public function getParentName()
	{
		return $this->parent;
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}
}
