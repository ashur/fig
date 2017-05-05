<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

abstract class Action
{
	/**
	 * @var	string
	 */
	protected $appName;

	/**
	 * @var	boolean
	 */
	protected $ignoreErrors = false;

	/**
	 * @var	boolean
	 */
	protected $ignoreOutput = false;

	/**
	 * @var	boolean
	 */
	public $includesProfile = false;

	/**
	 * @var	string
	 */
	public $name;

	/**
	 * @var	boolean
	 */
	public $privilegesEscalated = false;

	/**
	 * @var	string
	 */
	protected $profileName;

	/**
	 * @var	string
	 */
	protected $sudoPassword;

	/**
	 * @var	boolean
	 */
	protected $supportsPrivilegeEscalation = false;

	/**
	 * @var	string
	 */
	public $type = 'Action';

	/**
	 * @var	boolean
	 */
	public $usesDeprecatedSyntax = false;

	/**
	 * @var	boolean
	 */
	public $usesFigDirectory = false;

	/**
	 * @var	array
	 */
	protected $variables=[];

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		/* Validate 'name' value */
		if( !isset( $properties['name'] ) )
		{
			throw new \BadMethodCallException( "Missing required property 'name'." );
		}
		if( !is_string( $properties['name'] ) )
		{
			$stringName = var_export( $properties['name'], true );
			$stringName = str_replace( PHP_EOL, ' ', $stringName );

			throw new \InvalidArgumentException( "Invalid action name: '{$stringName}'" );
		}
		$this->name = $properties['name'];

		/* A collection of values users might use to mean `true` */
		$affirmativeValues = [true, 'true', 'yes'];

		/* Ignore Errors & Output */
		if( isset( $properties['ignore_errors'] ) )
		{
			$this->ignoreErrors = true;
		}
		if( isset( $properties['ignore_output'] ) )
		{
			$this->ignoreOutput = true;
		}

		/* Privilege Escalation */
		if( isset( $properties['sudo'] ) && $this->supportsPrivilegeEscalation )
		{
			if( in_array( strtolower( $properties['sudo'] ), $affirmativeValues ) )
			{
				$this->privilegesEscalated = true;
			}
		}
	}

	/**
	 * Perform the action and return output for display
	 *
	 * @return	array
	 */
	abstract public function execute();

	/**
	 * @return	string
	 */
	abstract public function getTitle();

	/**
	 * Called when adding action to profile
	 *
	 * @param	string	$appName
	 * @return	void
	 */
	public function setAppName( $appName )
	{
		$this->appName = $appName;
	}

	/**
	 * Called when adding action to profile
	 *
	 * @param	string	$profileName
	 * @return	void
	 */
	public function setProfileName( $profileName )
	{
		$this->profileName = $profileName;
	}

	/**
	 * @param	string	$sudoPassword
	 */
	public function setSudoPassword( $sudoPassword )
	{
		$this->sudoPassword = $sudoPassword;
	}

	/**
	 * @param	array	$variables
	 * @return	void
	 */
	public function setVariables( array $variables )
	{
		$this->variables = $variables;
	}
}
