<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

abstract class AbstractAction
{
	use \Fig\VarsTrait;

	/**
	 * @var	bool
	 */
	protected $isDeprecated=false;

	/**
	 * An array of parent profile names, in reverse chronological order
	 *
	 * @var	array
	 */
	protected $profileAncestry=[];

	/**
	 * Returns array of full profile ancestry
	 *
	 * @return	array
	 */
	public function getProfileAncestry() : array
	{
		return $this->profileAncestry;
	}

	/**
	 * Returns name of parent profile
	 *
	 * @throws	LogicException	If profile name not set
	 *
	 * @return	string
	 */
	public function getProfileName() : string
	{
		if( !$this->hasProfileName() )
		{
			throw new \LogicException( 'Profile name undefined' );
		}

		return $this->profileAncestry[0];
	}

	/**
	 * Returns whether profile name is set
	 *
	 * @return	bool
	 */
	public function hasProfileName() : bool
	{
		return count( $this->profileAncestry ) > 0;
	}

	/**
	 * Returns whether the action is deployable
	 *
	 * @return	bool
	 */
	public function isDeployable() : bool
	{
		return method_exists( $this, 'deploy' );
	}

	/**
	 * Returns whether action is deprecated
	 *
	 * @return	bool
	 */
	public function isDeprecated() : bool
	{
		return $this->isDeprecated;
	}

	/**
	 * Attempts to set value of variable
	 *
	 * @param	mixed	$variable
	 *
	 * @param	mixed	$value
	 *
	 * @throws	DomainException	If $value is not "booleanish"
	 *
	 * @return	void
	 */
	protected function setBooleanishValue( &$variable, $value )
	{
		if( is_bool( $value ) )
		{
			$variable = $value;
			return;
		}

		if( is_string( $value ) )
		{
			$value = strtolower( $value );
			switch( $value )
			{
				case 'true':
				case 'yes':
					$variable = true;
					return;
					break;

				case 'false':
				case 'no':
					$variable = false;
					return;
					break;
			}
		}

		throw new \DomainException( "'{$value}' is not booleanish" );
	}

	/**
	 * Sets name of profile to which the action belongs
	 *
	 * @param	string	$profileName
	 *
	 * @return	void
	 */
	public function setProfileName( string $profileName )
	{
		array_unshift( $this->profileAncestry, $profileName );
	}
}
