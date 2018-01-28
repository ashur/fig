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
	public function __construct( string $name  )
	{
		$this->name = $name;
	}

	/**
	 * Pushes an action object onto the end of the actions queue
	 *
	 * @param	Fig\Action\AbstractAction	$action
	 *
	 * @return
	 */
	public function addAction( Action\AbstractAction $action )
	{
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
