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
	 * @var	Fig\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * @var	array
	 */
	protected $repositories=[];

	/**
	 * @var	Fig\Shell\Shell
	 */
	protected $shell;

	/**
	 * @var	Fig\Output
	 */
	protected $output;

	/**
	 * @param	Fig\Filesystem\Filesystem	$filesystem
	 *
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @param	Fig\Output	$output
	 *
	 * @return	void
	 */
	public function __construct( Filesystem\Filesystem $filesystem, Shell\Shell $shell, Output $output )
	{
		$this->filesystem = $filesystem;
		$this->shell = $shell;
		$this->output = $output;
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
	 * Applies `$vars` to action objects and then deploys each action, writing
	 * results to output.
	 *
	 * @param	array	$actions	An array of deployable Fig\Action objects
	 *
	 * @param	array	$vars	An array of keys with scalar values
	 *
	 * @throws	\LogicException	If a non-deployable object is encountered
	 *
	 * @return	void
	 */
	public function deployActions( array $actions, array $vars )
	{
		foreach( $actions as $action )
		{
			if( !$action->isDeployable() )
			{
				throw new \LogicException( 'Non-deployable action encountered' );
			}

			$action->setVars( $vars );

			$this->output->writeActionHeader(
				$action->getType(),
				$action->getSubtitle(),
				$action->getName()
			);

			if( method_exists( $action, 'deployWithFilesystem' ) )
			{
				$result = $action->deployWithFilesystem( $this->filesystem );
			}
			elseif( method_exists( $action, 'deployWithShell' ) )
			{
				$result = $action->deployWithShell( $this->shell );
			}

			$this->output->writeActionResult( $result );
		}
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
	 * @return	void
	 */
	public function deployProfile( string $repositoryName, string $profileName )
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

		$this->deployActions( $actions, $vars );
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
