<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Fig\Action\Action;
use Huxtable\Core\File;

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
	 * @param	Huxtable\Core\File\File		$profileFile
	 * @param	string						$appName
	 * @return	Fig\Profile
	 */
	static public function getInstanceFromFile( File\File $profileFile, $appName )
	{
		$profileName = $profileFile->getBasename( '.yml' );
		$profile = new self( $profileName );

		$profileData = Fig::decodeFile( $profileFile );

		/* Extends */
		if( isset( $profileData['extends'] ) )
		{
			$profile->setParentName( $profileData['extends'] );
		}

		/* Actions */
		if( isset( $profileData['actions'] ) )
		{
			foreach( $profileData['actions'] as $actionData )
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
		foreach( $this->actions as $action )
		{
			if( get_class( $action ) == 'Fig\Action\File' )
			{
				$action->updateAssetsFromTarget();
			}
		}
	}
}
