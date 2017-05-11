<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

abstract class Action extends \Fig\Model
{
	/**
	 * @var	string
	 */
	protected $appName;

	/**
	 * @var	boolean
	 */
	public $ignoreErrors = false;

	/**
	 * @var	boolean
	 */
	public $ignoreOutput = false;

	/**
	 * @var	boolean
	 */
	public $includesProfile = false;

	/**
	 * @var	string
	 */
	public $name;

	/**
	 * @var	string
	 */
	protected $profileName;

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
	protected $variables = [];

	/**
	 * @param	array	$properties
	 * @return	void
	 */
	public function __construct( array $properties )
	{
		/*
		 * Define object properties shared by all Actions
		 */
		$this->defineProperty( 'name', true, 'self::isStringish' );

		$this->defineProperty( 'ignore_errors', false, 'self::isBooleanish', function( $value )
		{
			if( is_bool( $value ) )
			{
				$this->ignoreErrors = $value;
				return;
			}

			$value = strtolower( $value );
			$this->ignoreErrors = in_array( $value, $this->truthyValues, true ) ? true : false;
		});

		$this->defineProperty( 'ignore_output', false, 'self::isBooleanish', function( $value )
		{
			if( is_bool( $value ) )
			{
				$this->ignoreOutput = $value;
				return;
			}

			$value = strtolower( $value );
			$this->ignoreOutput = in_array( $value, $this->truthyValues, true ) ? true : false;
		});

		$this->setPropertyValues( $properties );
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
	 * @param	array	$variables
	 * @return	void
	 */
	public function setVariables( array $variables )
	{
		$this->variables = $variables;
	}
}
