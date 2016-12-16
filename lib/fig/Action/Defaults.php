<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig;

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
	 * The `defaults` command to be run
	 *
	 * @var	string
	 */
	protected $command;

	/**
	 * A human-readable summary of the `defaults` command
	 *
	 * @var	string
	 */
	protected $commandSummary;

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

		Fig\Fig::validateRequiredKeys( $properties['defaults'], ['action','domain'] );

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

		/* Human-readable action label */
		switch( $this->action )
		{
			case self::READ:
				$this->actionName = 'read';
				break;

			case self::WRITE:
				$this->actionName = 'write';
				break;

			case self::DELETE:
				$this->actionName = 'delete';
				break;
		}

		/* Build command */
		$this->command = "defaults {$this->actionName} {$this->domain}";
		$this->commandSummary = "{$this->domain}";

		/* Key */
		if( !is_null( $this->key ) )
		{
			$this->command .= " {$this->key}";
			$this->commandSummary .= " {$this->key}";
		}

		/* Value (write only) */
		if( $this->action == self::WRITE && !is_null( $this->value ) )
		{
			$this->command .= " {$this->value}";
		}
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @param	string	$username
	 * @param	string	$password
	 * @return	array
	 */
	public function execute()
	{
		/* Replace variables */
		$this->command = Fig\Fig::replaceVariables( $this->command, $this->variables );

		/* Results */
		exec( "{$this->command} 2>&1", $output, $exitCode );

		$result['title'] = $this->getTitle();
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
		if( $this->action == self::WRITE )
		{
			$result['output'] = $this->value;
		}

		return $result;
	}

	/**
	 * @return	string
	 */
	public function getTitle()
	{
		$title = "{$this->actionName} | {$this->commandSummary}";
		$title = Fig\Fig::replaceVariables( $title, $this->variables );

		return $title;
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
