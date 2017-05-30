<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Core\File;
use Fig\Action;

class Profile extends Model
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
		/*
		 * Define properties
		 */
		$this->defineProperty( 'name', true, 'self::isStringish' );
		$this->defineProperty( 'appName', true, 'self::isStringish' );

		$properties['name'] = $name;
		$properties['appName'] = $appName;

		$this->setPropertyValues( $properties );
	}

	/**
	 * @param	Fig\Action\Action	$action
	 * @return	self
	 */
	public function addAction( Action\Action $action )
	{
		if( $action instanceof Action\Profile )
		{
			if( $this->name == $action->getIncludedProfileName() )
			{
				throw new \InvalidArgumentException( "Profile '{$this->name}' cannot include itself" );
			}
		}

		$action->setAppName( $this->appName );
		$action->setProfileName( $this->name );

		$this->actions[] = $action;
		return $this;
	}

	/**
	 * Apply the child $profile's properties to this object then return the result
	 *
	 * @param	Fig\Profile		$childProfile
	 * @return	Fig\Profile
	 */
	public function extendWith( Profile $childProfile )
	{
		$extendedProfile = clone $this;

		// A profile cannot extend itself
		if( $extendedProfile->getName() == $childProfile->getName() )
		{
			$extendedProfile->setParentName( null );
			return $extendedProfile;
		}

		/* Rename */
		$extendedProfile->setName( $childProfile->getName() );

		/*
		 * Actions
		 */
		/* Add extending profile's actions */
		$actions = $childProfile->getActions();
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
		$profileVariables = $childProfile->getVariables();
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
				$action = new Action\Profile( $profileItem );
			}
			/* Actions */
			else
			{
				if( !is_array( $profileItem ) )
				{
					$stringValue = Fig::getStringRepresentation( $profileItem );
					$exceptionMessage = sprintf( 'Invalid YAML found in \'%s\': %s', $profileFile->getBasename(), $stringValue );

					throw new \InvalidArgumentException( $exceptionMessage );
				}
				$action = Action\Action::getInstanceFromData( $profileItem );
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
