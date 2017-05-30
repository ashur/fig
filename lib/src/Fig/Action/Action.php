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
	public function getAppName()
	{
		return $this->appName;
	}

	/**
	 * @param	array	$data
	 * @return	Fig\Action\Action
	 */
	public static function getInstanceFromData( array $data )
	{
		/* Get instance of Action class */
		$actionClasses['command']	= 'Command';
		$actionClasses['defaults']	= 'Defaults';
		$actionClasses['file']		= 'File';

		foreach( $actionClasses as $dataKey => $actionClass )
		{
			if( isset( $data[$dataKey] ) )
			{
				$className = "Fig\Action\\{$actionClass}";
				$action = new $className( $data );
				return $action;
			}
		}

		$stringValue = \Fig\Fig::getStringRepresentation( $data );
		throw new \InvalidArgumentException( "Unsupported action definition: '{$stringValue}'. See https://github.com/ashur/fig/wiki/Actions" );
	}

	/**
	 * @return	string
	 */
	public function getProfileName()
	{
		return $this->profileName;
	}

	/**
	 * @return	string
	 */
	abstract public function getTitle();

	/**
	 * @return	array
	 */
	public function getVariables()
	{
		return $this->variables;
	}

	/**
	 * Called when adding action to profile; used primarily by Fig\Action\File,
	 *    but made available to all Action\Action classes
	 *
	 * @param	string	$appName
	 * @return	void
	 */
	public function setAppName( $appName )
	{
		$this->appName = $appName;
	}

	/**
	 * Called when adding action to profile; used primarily by Fig\Action\File,
	 *    but made available to all Action\Action classes
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
		foreach( $variables as $key => $value )
		{
			$this->variables[$key] = $value;
		}
	}
}
