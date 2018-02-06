<?php

/*
 * This file is part of Fig
 */
namespace Fig;

class Repository
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
	 *
	 * @return	void
	 */
	public function __construct( string $name )
	{
		$this->name = $name;
	}

	/**
	 * Adds a profile object to the repository
	 *
	 * @param	Fig\Profile	$profile
	 *
	 * @return	void
	 */
	public function addProfile( Profile $profile )
	{
		$profileName = $profile->getName();
		$this->profiles[$profileName] = $profile;
	}

	/**
	 * Returns repository name
	 *
	 * @return	string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Returns a profile object for the given profile name
	 *
	 * @param	string	$profileName
	 *
	 * @throws	Fig\Exception\RuntimeException	If profile is undefined
	 *
	 * @return	Fig\Profile
	 */
	public function getProfile( string $profileName ) : Profile
	{
		if( !$this->hasProfile( $profileName ) )
		{
			$exceptionMessage = sprintf( 'No such profile: "%s/%s"', $this->getName(), $profileName );
			throw new Exception\RuntimeException( $exceptionMessage, Exception\RuntimeException::PROFILE_NOT_FOUND );
		}

		$profile = $this->profiles[$profileName];

		return $profile;
	}

	/**
	 * Returns array of Action objects for the given profile name.
	 *
	 * If the requested profile extends or includes other profiles from this
	 * repository, actions from those additional profiles will be automatically
	 * integrated into the array.
	 *
	 * @param	string	$profileName
	 *
	 * @return	array
	 */
	public function getProfileActions( string $profileName ) : array
	{
		$actions = [];

		$profile = $this->getProfile( $profileName );
		$originalActions = $profile->getActions();

		foreach( $originalActions as $originalAction )
		{
			/* Integrate actions from `include`-d profiles */
			if( method_exists( $originalAction, 'getIncludedProfileName' ) )
			{
				$includedProfileName = $originalAction->getIncludedProfileName();
				$includedActions = $this->getProfileActions( $includedProfileName );

				$actions = array_merge( $actions, $includedActions );
			}

			/* Integrate actions from `extend`-ed profiles */
			elseif( method_exists( $originalAction, 'getExtendedProfileName' ) )
			{
				$extendedProfileName = $originalAction->getExtendedProfileName();
				$extendedActions = $this->getProfileActions( $extendedProfileName );

				/* Extend original actions by setting the new profile name */
				foreach( $extendedActions as $extendedAction )
				{
					$extendedAction->setProfileName( $profileName );
					$actions[] = $extendedAction;
				}
			}

			/* Include the action directly */
			else
			{
				$actions[] = $originalAction;
			}
		}

		return $actions;
	}

	/**
	 * Returns whether profile is defined
	 *
	 * @param	string	$profileName
	 *
	 * @return	bool
	 */
	public function hasProfile( string $profileName ) : bool
	{
		return isset( $this->profiles[$profileName] );
	}
}
