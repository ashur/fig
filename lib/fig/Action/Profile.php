<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Fig;

class Profile extends Action
{
	/**
	 * @var	string
	 */
	protected $includedProfileName;

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		parent::__construct( $properties );

		Fig::validateRequiredKeys( $properties, ['include'] );

		$this->includedProfileName = $properties['include'];
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @return	array
	 */
	public function execute()
	{
		$fig = new Fig();
		$fig->deployProfile( $this->appName, $this->includedProfileName );

		$result['title'] = "include {$this->includedProfileName}";
		$result['error'] = false;
		$result['output'] = null;
		$result['silent'] = true;

		return $result;
	}

	/**
	 * A stub, since we don't need to print out profile inclusion actions
	 * @return	string
	 */
	public function getTitle(){}
}
