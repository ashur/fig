<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Defaults;

use Fig\Engine;
use Fig\Exception;

class WriteDefaultsAction extends BaseDefaultsAction
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
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine )
	{
		try
		{
			$this->preDeploy( $engine );
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
		$result = $engine->executeCommand( 'defaults', $commandArguments );

		/* Populate output, error */
		$this->didError = $result['exitCode'] !== 0;

		if( $this->didError )
		{
			$this->outputString = implode( PHP_EOL, $result['output'] );
		}
		else
		{
			$this->outputString = $this->getValue();
		}
	}
}
