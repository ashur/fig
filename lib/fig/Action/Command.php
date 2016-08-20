<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Fig;

class Command extends Action
{
	/**
	 * @var	int
	 */
	public $command;

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		parent::__construct( $properties );

		Fig::validateRequiredKeys( $properties, ['command'] );

		$this->command = $properties['command'];
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @return	array
	 */
	public function execute()
	{
		/* Results */
		exec( "{$this->command} 2>&1", $output, $exitCode );

		$result['title'] = $this->name;
		$result['error'] = $exitCode != 0;
		$result['output'] = $output;

		/* Modify Output */
		if( $this->ignoreOutput )
		{
			$result['output'] = null;
		}
		if( $this->ignoreErrors && $result['error'] == true )
		{
			$result['error'] = false;
			$result['output'] = null;
		}

		return $result;
	}
}
