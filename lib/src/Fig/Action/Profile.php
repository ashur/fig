<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig;

class Profile extends Action
{
	/**
	 * @var	string
	 */
	protected $includedProfileName;

	/**
	 * @var	type
	 */
	public $includesProfile = true;

	/**
	 * @var	string
	 */
	public $type = 'Include';

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		/*
		 * Define properties
		 */
		$this->defineProperty( 'include', true, 'self::isStringish', function( $value )
		{
			$this->includedProfileName = $value;
		});

		$this->setPropertyValues( $properties );
	}

	/**
	 * A stub, since profile deployment is handled by Fig\Fig
	 *
	 * @return	array
	 */
	public function execute(){}

	/**
	 * @return	string
	 */
	public function getIncludedProfileName()
	{
		return $this->includedProfileName;
	}

	/**
	 * A stub, since we don't need to print out profile inclusion actions
	 *
	 * @return	string
	 */
	public function getTitle(){}
}
