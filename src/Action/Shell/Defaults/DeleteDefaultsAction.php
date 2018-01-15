<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Exception;
use Fig\Shell\Shell;

class DeleteDefaultsAction extends BaseDefaultsAction
{
	/**
	 * @var	string
	 */
	protected $methodName='delete';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$domain
	 *
	 * @param	string	$key
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $domain, string $key=null )
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->key = $key;
	}

	/**
	 * Attempts to execute `defaults read <domain> [<key>]`
	 *
	 * @param	Fig\Shell\Shell	$shell
	 *
	 * @return	void
	 */
	public function deploy( Shell $shell )
	{
		try
		{
			$this->preDeploy( $shell );
		}
		catch( Exception\Exception $e )
		{
			$this->didError = true;
			$this->outputString = $e->getMessage();

			return;
		}

		/* Build command */
		$commandArguments[] = $this->methodName;
		$commandArguments[] = $this->getDomain();

		if( $this->hasKey() )
		{
			$commandArguments[] = $this->getKey();
		}

		/* Execute command */
		$result = $shell->executeCommand( 'defaults', $commandArguments );

		/* Populate output, error */
		$this->didError = $result->getExitCode() !== 0;

		if( $this->didError )
		{
			$this->outputString = implode( PHP_EOL, $result->getOutput() );
		}
		else
		{
			$this->outputString = self::STRING_STATUS_SUCCESS;
		}
	}
}
