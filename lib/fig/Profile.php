<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Fig\Action\Action;
use Huxtable\Core\File;

class Profile
{
	const ASSETS_DIRNAME = 'source';
	const CONFIG_FILENAME = 'config.yml';

	/**
	 * @var	array
	 */
	protected $actions=[];

	/**
	 * @var	string
	 */
	protected $appName='';

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
	}

	/**
	 * @param	Fig\Action\Action	$action
	 * @return	self
	 */
	public function addAction( Action $action )
	{
		$this->actions[] = $action;
		return $this;
	}

	/**
	 * Apply this profile's properties to a parent profile
	 *   then return the resulting profile object
	 *
	 * @param	Fig\Profile		$profile
	 * @return	Fig\Profile
	 */
	public function extendWith( Profile $profile )
	{
		$extendedProfile = clone $this;

		// A profile cannot extend itself
		if( $extendedProfile->getName() == $profile->getName() )
		{
			$extendedProfile->setParentName( null );
			return $extendedProfile;
		}

		/* Actions */
		$actions = $profile->getActions();
		foreach( $actions as $action )
		{
			$extendedProfile->addAction( $action );
		}

		return $extendedProfile;
	}

	/**
	 * @return	array
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * @param	Huxtable\Core\File\Directory	$dirProfile
	 * @param	string							$appName
	 * @return	Fig\Profile
	 */
	static public function getInstanceFromDirectory( File\Directory $dirProfile, $appName )
	{
		$profileName = $dirProfile->getBasename();
		$profile = new self( $profileName );

		// Populate fields from config file
		$configFile = $dirProfile->child( self::CONFIG_FILENAME );

		if( !$configFile->exists() )
		{
			throw new \Exception( 'Invalid profile: configuration file not found' );
		}

		$config = Fig::decodeFile( $configFile );

		/* Extends */
		if( isset( $config['extends'] ) )
		{
			$profile->setParentName( $config['extends'] );
		}

		/* Actions */
		if( isset( $config['actions'] ) )
		{
			foreach( $config['actions'] as $actionData )
			{
				$action = Fig::getActionInstanceFromData( $actionData, $appName, $profileName );
				$profile->addAction( $action );
			}
		}

		return $profile;
	}

	/**
	 * @return	string
	 */
	public function getAppName()
	{
		return $this->appName;
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
	 * @param	string	$name
	 * @return	void
	 */
	public function setName( $name )
	{
		$this->name = $name;
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
	 * @return	void
	 */
	public function updateAssetsFromTarget()
	{
		foreach( $this->assets as $asset )
		{
			$asset->replaceSourceWithTarget();
		}
	}

	/**
	 * Write contents of profile to disk
	 *
	 * @param	Huxtable\Core\File\Directory	$dirProfile
	 * @return	void
	 */
	public function write( File\Directory $dirProfile )
	{
		$configData = [];
		$configFile = $dirProfile->child( self::CONFIG_FILENAME );

		if( !$dirProfile->exists() )
		{
			$dirProfile->create();
		}

		// Populate configuration contents
		$configData['commands'] = $this->commands;
		$configData['files'] = $this->assets;

		if( isset( $this->parentName ) )
		{
			$configData['extends'] = $this->parentName;
		}

		$configContents = Fig::encodeData( $configData );
		$configFile->putContents( $configContents );
	}
}
