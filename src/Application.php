<?php

/**
 * This file is part of Fig
 */
namespace Fig;

class Application
{
	const NAME    = 'fig';
	const VERSION = '0.5a';
	const PHP_MIN = '7.0';

	/**
	 * @var	array
	 */
	protected $repositories=[];

	/**
	 * Adds a Repository object
	 *
	 * @param	Fig\Repository	$repository
	 *
	 * @return	void
	 */
	public function addRepository( Repository $repository )
	{
		$repositoryName = $repository->getName();
		$this->repositories[$repositoryName] = $repository;
	}

	/**
	 * Returns whether repository is defined
	 *
	 * @param	string	$repositoryName
	 *
	 * @return	bool
	 */
	public function hasRepository( string $repositoryName ) : bool
	{
		return isset( $this->repositories[$repositoryName] );
	}
}
