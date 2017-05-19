<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Core\File;
use Fig\Action\Action;

class App extends Model
{
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
		/*
		 * Define object properties
		 */
		$this->defineProperty( 'name', true, function( $value )
		{
			if( !$this->isStringish( $value ) )
			{
				return false;
			}

			$invalidCharacters = ['/', '\\', ':', ' '];
			foreach( $invalidCharacters as $invalidCharacter )
			{
				if( substr_count( $value, $invalidCharacter ) > 0 )
				{
					$invalidCharactersString = sprintf( '"%s"', implode( '', $invalidCharacters ) );
					$invalidPropertyMessage = sprintf( "Invalid app name '{$value}': Contains one or more invalid characters, {$invalidCharactersString}" );

					throw new \InvalidArgumentException( $invalidPropertyMessage );
				}
			}

			return true;
		});

		$this->setPropertyValues( ['name' => $name] );
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
		$appName = $dirApp->getBasename();
		$app = new self( $appName );

		// Only .yml files
		$fileFilter = new File\Filter();
		$fileFilter->setDefaultMethod( File\Filter::METHOD_INCLUDE );
		$fileFilter->includeFileExtension( 'yml' );

		/* Profiles */
		$profileFiles = $dirApp->children( $fileFilter );

		foreach( $profileFiles as $profileFile )
		{
			$profile = Profile::getInstanceFromFile( $profileFile );
			$app->addProfile( $profile );
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
			throw new \OutOfRangeException( "Profile '{$this->name}/{$profileName}' not found." );
		}

		$profile = $this->profiles[$profileName];
		while( !is_null( $profile->getParentName() ) )
		{
			$parentProfileName = $profile->getParentName();
			if( !isset( $this->profiles[$parentProfileName] ) )
			{
				throw new \OutOfRangeException( "Profile '{$this->name}/{$parentProfileName}' not found." );
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
	 * @param	string	$profileName
	 * @return	void
	 */
	public function removeProfile( $profileName )
	{
		if( isset( $this->profiles[$profileName] ) )
		{
			unset( $this->profiles[$profileName] );
		}
	}
}
