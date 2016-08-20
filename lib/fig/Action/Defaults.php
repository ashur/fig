<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Fig;

class Defaults extends Action
{
	const READ   = 1;
	const WRITE  = 2;
	const DELETE = 4;

	/**
	 * @var	int
	 */
	public $action = self::READ;

	/**
	 * @var	string
	 */
	public $domain;

	/**
	 * @var	string
	 */
	public $key;

	/**
	 * @var	string
	 */
	public $type = 'Defaults';

	/**
	 * @var	string
	 */
	public $value='';

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		parent::__construct( $properties );

		Fig::validateRequiredKeys( $properties['defaults'], ['action','domain'] );

		$this->domain = $properties['defaults']['domain'];
		$this->setAction( $properties['defaults']['action'] );

		if( isset( $properties['defaults']['key'] ) )
		{
			$this->key = $properties['defaults']['key'];
		}

		if( isset( $properties['defaults']['value'] ) )
		{
			$this->value = $properties['defaults']['value'];
		}
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @return	array
	 */
	public function execute()
	{
		switch( $this->action )
		{
			case self::READ:
				$action = 'read';
				break;

			case self::WRITE:
				$action = 'write';
				break;

			case self::DELETE:
				$action = 'delete';
				break;
		}

		$command = "defaults {$action} {$this->domain}";
		$commandSummary = "{$this->domain}";

		/* Key */
		if( !is_null( $this->key ) )
		{
			$command .= " {$this->key}";
			$commandSummary .= " {$this->key}";
		}

		/* Value (write only) */
		if( $this->action == self::WRITE && !is_null( $this->value ) )
		{
			$command .= " {$this->value}";
		}

		/* Results */
		exec( "{$command} 2>&1", $output, $exitCode );

		$result['title'] = "{$action} | {$commandSummary}";
		$result['error'] = $exitCode != 0;
		$result['output'] = $output;

		/* Modify Output */
		if( $this->ignoreOutput )
		{
			$result['output'] = null;
		}
		if( $this->ignoreErrors )
		{
			$result['error'] = false;
			$result['output'] = null;
		}
		if( $this->action == self::WRITE )
		{
			$result['output'] = $this->value;
		}

		return $result;
	}

	/**
	 * @param	string	$action
	 * @return	void
	 */
	public function setAction( $action )
	{
		$action = strtolower( $action );

		switch( $action )
		{
			case 'delete':
				$this->action = self::DELETE;
				break;

			case 'write':
				$this->action = self::WRITE;
				break;
		}
	}

	/**
	 * @param	string	$domain
	 * @return	void
	 */
	public function setDomain( $domain )
	{
		$this->domain = $domain;
	}
}
