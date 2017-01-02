<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Core\File;
use Fig\Action;

class Profile
{
	const ASSETS_DIRNAME = 'assets';

	/**
	 * @var	array
	 */
	protected $actions=[];

	/**
	 * @var	string
	 */
	protected $appName;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @var	string
	 */
	protected $parentName;

	/**
	 * @var	array
	 */
	protected $variables=[];

	/**
	 * @param	string	$name
	 * @return	void
	 */
	public function __construct( $name, $appName )
	{
		$this->name = $name;
		$this->appName = $appName;
	}

	/**
	 * @param	Fig\Action\Action	$action
	 * @return	self
	 */
	public function addAction( Action\Action $action )
	{
		$action->setAppName( $this->appName );
		$action->setProfileName( $this->name );

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

		/* Rename */
		$extendedProfile->setName( $profile->getName() );

		/*
		 * Actions
		 */
		/* Add extending profile's actions */
		$actions = $profile->getActions();
		foreach( $actions as $action )
		{
			$action->setVariables( $this->variables );
			$extendedProfile->addAction( $action );
		}
		/* Set new profile name on existing actions */
		foreach( $extendedProfile->actions as &$action )
		{
			$action->setProfileName( $extendedProfile->getName() );
		}

		/* Variables */
		$profileVariables = $profile->getVariables();
		$extendedProfile->setVariables( $profileVariables );

		return $extendedProfile;
	}

	/**
	 * @return	array
	 */
	public function getActions()
	{
		foreach( $this->actions as &$action )
		{
			$action->setVariables( $this->variables );
		}

		return $this->actions;
	}

	/**
	 * @param	Huxtable\Core\File\File		$profileFile
	 * @return	Fig\Profile
	 */
	static public function getInstanceFromFile( File\File $profileFile )
	{
		$appName = $profileFile->parent()->getBasename();

		$profileName = $profileFile->getBasename( '.yml' );
		$profile = new self( $profileName, $appName );

		$profileData = Fig::decodeFile( $profileFile );
		foreach( $profileData as $profileItem )
		{
			/* Variables */
			if( isset( $profileItem['vars'] ) )
			{
				$profile->setVariables( $profileItem['vars'] );
				continue;
			}

			/* Extend */
			if( isset( $profileItem['extend'] ) )
			{
				$profile->setParentName( $profileItem['extend'] );
				continue;
			}

			/* Include */
			if( isset( $profileItem['include'] ) )
			{
				$profileItem['name'] = "import {$profileItem['include']}";
				$action = new Action\Profile( $profileItem );
			}
			/* Actions */
			else
			{
				$action = Fig::getActionInstanceFromData( $profileItem );
			}

			$profile->addAction( $action );
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
	 * @return	array
	 */
	public function getVariables()
	{
		return $this->variables;
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
	 * @param	array	$variables
	 * @return	void
	 */
	public function setVariables( array $variables )
	{
		foreach( $variables as $key => $value )
		{
			if( is_scalar( $value ) )
			{
				$this->variables[$key] = $value;
			}
		}
	}

	/**
	 * @param	Cranberry\Core\File\Directory	$figDirectory
	 */
	public function updateAssetsFromTarget( File\Directory $figDirectory )
	{
		foreach( $this->actions as $action )
		{
			if( get_class( $action ) == 'Fig\Action\File' )
			{
				$action->setFigDirectory( $figDirectory );
				$action->updateAssetsFromTarget( $figDirectory );
			}
		}
	}
}
