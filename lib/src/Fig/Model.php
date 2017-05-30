<?php

/*
 * This file is part of Fig
 */
namespace Fig;

class Model
{
	/**
	 * Values users might use to mean `false`
	 *
	 * @var	array
	 */
	protected $falseyValues = [false, 'false', 'no'];

	/**
	 * @var	array
	 */
	protected $propertyDefinitions = [];

	/**
	 * Values users might use to mean `true`
	 *
	 * @var	array
	 */
	protected $truthyValues = [true, 'true', 'yes'];

	/**
	 * @param	string		$name
	 * @param	mixed		$required		boolean or callable
	 * @param	callable	$validator
	 * @param	callable	$setter
	 * @return	void
	 */
	public function defineProperty( $name, $required, callable $validator, callable $setter=null )
	{
		if( !is_callable( $required ) )
		{
			$required = $required === true;
		}

		$definition = [
			'name' => $name,
			'required' => $required,
			'validator' => $validator,
			'setter' => $setter
		];

		$this->propertyDefinitions[] = $definition;
	}

	/**
	 * @param	mixed	$value
	 * @return	boolean
	 */
	public function isBooleanish( $value )
	{
		if( is_bool( $value ) )
		{
			return true;
		}

		$booleanishValues = array_merge( $this->truthyValues, $this->falseyValues );
		return in_array( strtolower( $value ), $booleanishValues, true );
	}

	/**
	 * @param	mixed	$value
	 * @return	boolean
	 */
	public function isStringish( $value )
	{
		$isStringish = false;
		$isStringish = $isStringish || is_string( $value );
		$isStringish = $isStringish || is_numeric( $value );

		return $isStringish;
	}

	/**
	 * Compare values of properties parsed from YAML files to definitions
	 *
	 * @param	array	$properties		Array of user-provided property values
	 */
	public function setPropertyValues( array $properties )
	{
		foreach( $this->propertyDefinitions as $propertyDefinition )
		{
			$propertyName = $propertyDefinition['name'];

			/* Uses 'array_key_exists' to support required null values */
			if( array_key_exists( $propertyName, $properties ) )
			{
				$propertyValue = $properties[$propertyName];
				$didPassValidation = call_user_func( $propertyDefinition['validator'], $propertyValue );

				if( !$didPassValidation )
				{
					$stringValue = Fig::getStringRepresentation( $propertyValue );
					$invalidPropertyMessage = sprintf( Fig::STRING_INVALID_PROPERTY_VALUE, $propertyName, $stringValue );

					throw new \InvalidArgumentException( $invalidPropertyMessage );
				}

				/* Set property value */
				if( $propertyDefinition['setter'] != null )
				{
					$setter = $propertyDefinition['setter'];
					if( $setter instanceof \Closure )
					{
						$setter = $setter->bindTo( $this );
					}

					call_user_func( $setter, $propertyValue );
				}
				else
				{
					$this->$propertyName = $propertyValue;
				}
			}
			else
			{
				if( is_callable( $propertyDefinition['required'] ) )
				{
					$propertyIsRequired = call_user_func( $propertyDefinition['required'] );
				}
				else
				{
					$propertyIsRequired = $propertyDefinition['required'];
				}

				if( $propertyIsRequired )
				{
					$missingPropertyMessage = sprintf( FIG::STRING_MISSING_REQUIRED_PROPERTY, $propertyName );
					throw new \InvalidArgumentException( $missingPropertyMessage );
				}
			}
		}
	}
}
