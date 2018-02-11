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
	 * @var	Fig\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * @var	Fig\Shell\Shell
	 */
	protected $shell;

	/**
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @return	void
	 */
	public function __construct( Filesystem\Filesystem $filesystem, Shell\Shell $shell )
	{
		$this->filesystem = $filesystem;
		$this->shell = $shell;
	}

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
	 * Applies `$vars` to action objects and then deploys each action
	 *
	 * @param	array	$actions	An array of deployable Fig\Action objects
	 *
	 * @param	array	$vars	An array of keys with scalar values
	 *
	 * @throws	\LogicException	If a non-deployable object is encountered
	 *
	 * @return	array 	An array of Fig\Action\Result objects
	 */
	public function deployActions( array $actions, array $vars ) : array
	{
		$results = [];

		foreach( $actions as $action )
		{
			if( !$action->isDeployable() )
			{
				throw new \LogicException( 'Non-deployable action encountered' );
			}

			$action->setVars( $vars );

			if( method_exists( $action, 'deployWithFilesystem' ) )
			{
				$results[] = $action->deployWithFilesystem( $this->filesystem );
			}
			elseif( method_exists( $action, 'deployWithShell' ) )
			{
				$results[] = $action->deployWithShell( $this->shell );
			}
		}

		return $results;
	}

	/**
	 * Gets actions and vars from specified profile and then deploys them
	 *
	 * @param	string	$repositoryName
	 *
	 * @param	string	$profileName
	 *
	 * @throws	Fig\Exception\RuntimeException	If repository is undefined
	 *
	 * @return	array
	 */
	public function deployProfile( string $repositoryName, string $profileName ) : array
	{
		if( !$this->hasRepository( $repositoryName ) )
		{
			$exceptionMessage = sprintf( 'No such repository: "%s"', $repositoryName );
			throw new Exception\RuntimeException( $exceptionMessage, Exception\RuntimeException::REPOSITORY_NOT_FOUND );
		}

		$repository = $this->repositories[$repositoryName];
		$profile = $repository->getProfile( $profileName );

		$actions = $profile->getActions();
		$vars = $profile->getVars();

		$results = $this->deployActions( $actions, $vars );

		return $results;
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
