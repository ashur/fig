<?php

/*
 * This file is part of Fig
 */
namespace Fig;

class Profile
{
	use \Fig\VarsTrait;

	/**
	 * @var	array
	 */
	protected $actions=[];

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @param	string	$name
	 *
	 * @return	void
	 */
	public function __construct( string $name )
	{
		$this->name = $name;
	}

	/**
	 * Pushes an action object onto the end of the actions queue
	 *
	 * @param	Fig\Action\AbstractAction	$action
	 *
	 * @throws	Fig\Exception\ProfileSyntaxException	If action includes or extends the current profile
	 *
	 * @return
	 */
	public function addAction( Action\AbstractAction $action )
	{
		if( method_exists( $action, 'getIncludedProfileName' ) )
		{
			if( $action->getIncludedProfileName() == $this->getName() )
			{
				$exceptionMessage = sprintf( 'Recursive inclusion of profile \'%s\'', $this->getName() );
				throw new Exception\ProfileSyntaxException( $exceptionMessage, Exception\ProfileSyntaxException::RECURSION );
			}
		}

		if( method_exists( $action, 'getExtendedProfileName' ) )
		{
			if( $action->getExtendedProfileName() == $this->getName() )
			{
				$exceptionMessage = sprintf( 'Recursive extension of profile \'%s\'', $this->getName() );
				throw new Exception\ProfileSyntaxException( $exceptionMessage, Exception\ProfileSyntaxException::RECURSION );
			}
		}

		$action->setProfileName( $this->getName() );
		$this->actions[] = $action;
	}

	/**
	 * Returns array of Action objects
	 *
	 * @return	array
	 */
	public function getActions() : array
	{
		return $this->actions;
	}

	/**
	 * Returns profile name
	 *
	 * @return	string
	 */
	public function getName() : string
	{
		return $this->name;
	}
}
