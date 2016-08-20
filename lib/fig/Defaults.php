<?php

/*
 * This file is part of Fig
 */
namespace Fig;

class Defaults
{
	const READ = 0;
	const WRITE = 1;
	const DELETE = 2;

	/**
	 * @var	int
	 */
	protected $action;

	/**
	 * @var	boolean
	 */
	public $ignoreErrors=false;

	/**
	 * @var	string
	 */
	protected $key='';

	/**
	 * @var	mixed
	 */
	protected $value;

	/**
	 * @param	string	$name		Name to print
	 * @param	string	$domain		ex., com.panic.Transmit
	 * @param	string	$key		Key name
	 * @return	void
	 */
	public function __construct( $name, $domain )
	{
		$this->name = $name;
		$this->domain = $domain;
	}

	/**
	 * Build the 'defaults' command and run it
	 *
	 * @return	array
	 */
	public function exec()
	{
		/*
		 * Build command
		 */
		if( $this->action == self::READ )
		{
			$commandBody = sprintf( 'read %s %s', $this->domain, $this->key );
		}
		if( $this->action == self::WRITE )
		{
			$commandBody = sprintf( 'write %s %s %s', $this->domain, $this->key, $this->value );
		}
		if( $this->action == self::DELETE )
		{
			if( !is_null( $this->key ) )
			{
				$commandBody = sprintf( 'delete %s %s', $this->domain, $this->key );
			}
			else
			{
				$commandBody = sprintf( 'delete %s', $this->domain );
			}
		}

		$command = "defaults {$commandBody}";

		exec( "{$command} 2>&1", $output, $exitCode );

		$result['command']  = $command;
		$result['output']   = $output;
		$result['exitCode'] = $exitCode;

		/* Slight deviation from normal output: show the value just written */
		if( $this->action == self::WRITE )
		{
			$result['output'] = [$this->value];
		}

		return $result;
	}

	/**
	 * @return	string
	 */
	public function getAction()
	{
		switch( $this->action )
		{
			case self::DELETE:
				return 'delete';
				break;

			case self::WRITE:
				return 'write';
				break;

			case self::READ:
				return 'read';
				break;
		}
	}

	/**
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param	array	$data
	 * @return	self
	 */
	static public function getInstanceFromData( array $data )
	{
		// Check required fields
		$requiredFields = ['name','action','domain'];

		foreach( $requiredFields as $requiredField )
		{
			if( !isset( $data[$requiredField] ) )
			{
				throw new \Exception( "Invalid profile: missing asset field '{$requiredField}'" );
			}
		}

		$defaults = new self( $data['name'], $data['domain'] );

		switch( $data['action'] )
		{
			case 'delete':
				$defaults->setAction( self::DELETE );
				break;

			case 'write':
				$defaults->setAction( self::WRITE );
				break;

			default:
				$defaults->setAction( self::READ );
				break;
		}

		/* Key */
		if( isset( $data['key' ] ) )
		{
			$defaults->setKey( $data['key'] );
		}

		/* Value */
		if( isset( $data['value' ] ) )
		{
			$defaults->setValue( $data['value'] );
		}

		/* Errors */
		if( isset( $data['ignore_errors'] ) )
		{
			$defaults->ignoreErrors = $data['ignore_errors'] == true;
		}

		return $defaults;
	}

	/**
	 * @param	int		$action
	 * @return	void
	 */
	public function setAction( $action )
	{
		$this->action = $action;
	}

	/**
	 * @param	string	$key
	 * @return	void
	 */
	public function setKey( $key )
	{
		$this->key = $key;
	}

	/**
	 * @param	mixed	$value
	 * @return	void
	 */
	public function setValue( $value )
	{
		$this->value = $value;
	}
}
