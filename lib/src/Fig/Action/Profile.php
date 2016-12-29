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
		parent::__construct( $properties );

		Fig\Fig::validateRequiredKeys( $properties, ['include'] );

		$this->includedProfileName = $properties['include'];
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
	 * @return	string
	 */
	public function getTitle(){}
}
