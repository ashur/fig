<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Exception;
use Fig\Shell\Shell;

class WriteDefaultsAction extends AbstractDefaultsAction
{
	/**
	 * @var	string
	 */
	protected $methodName='write';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$domain
	 *
	 * @param	string	$key
	 *
	 * @param	string	$value
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $domain, string $key, string $value )
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->key = $key;
		$this->value = $value;
	}

	/**
	 * Attempts to execute `defaults write <domain> <key> <value>`
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
		$commandArguments[] = $this->getKey();
		$commandArguments[] = $this->getValue();

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
			$this->outputString = $this->getValue();
		}
	}
}
