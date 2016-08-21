<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Fig\Action\Action;
use Huxtable\Core\File;

class App
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
}
