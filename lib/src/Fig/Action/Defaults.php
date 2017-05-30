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
	 * @var	string
	 */
	public $actionName = 'read';

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
		/*
		 * Validate 'defaults' definition before continuing.
		 *
		 * Since 'defaults' is not stored as a property of the Defaults object, we
		 * don't use `defineProperty` or `setPropertyValues` for validation.
		 */
		if( !isset( $properties['defaults'] ) )
		{
			$missingPropertyMessage = sprintf( \Fig\Fig::STRING_MISSING_REQUIRED_PROPERTY, 'defaults' );
			throw new \InvalidArgumentException( $missingPropertyMessage );
		}

		if( !is_array( $properties['defaults'] ) )
		{
			$stringValue = \Fig\Fig::getStringRepresentation( $properties['defaults'] );
			$invalidPropertyMessage = sprintf( \Fig\Fig::STRING_INVALID_PROPERTY_VALUE, 'defaults', $stringValue );

			throw new \InvalidArgumentException( $invalidPropertyMessage );
		}

		/*
		 * Flatten 'defaults' properties with top-level properties, then validate
		 */
		$defaultsProperties = array_merge( $properties, $properties['defaults']);
		unset( $defaultsProperties['defaults'] );

		/* 'action' */
		$this->defineProperty( 'action', true, function( $value )
		{
			if( !is_string( $value ) )
			{
				return false;
			}

			return in_array( strtolower( $value ), ['read', 'write', 'delete'] );

		}, array( $this, 'setAction' ));

		/* 'domain' */
		$this->defineProperty( 'domain', true, 'self::isStringish' );

		/* 'key' */
		$this->defineProperty( 'key', false, 'self::isStringish' );

		/* 'value' */
		$this->defineProperty( 'value', false, 'self::isStringish' );

		parent::__construct( $defaultsProperties );

		/*
		 * Build command
		 */
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
	public function setAction( $actionName )
	{
		$actionName = strtolower( $actionName );

		switch( $actionName )
		{
			case 'read':
				$this->action = self::READ;
				$this->actionName = 'read';
				break;

			case 'delete':
				$this->action = self::DELETE;
				$this->actionName = 'delete';
				break;

			case 'write':
				$this->action = self::WRITE;
				$this->actionName = 'write';
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
