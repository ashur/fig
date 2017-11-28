<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Defaults;

use Fig\Engine;
use Fig\Exception;

class ReadDefaultsAction extends BaseDefaultsAction
{
	/**
	 * @var	string
	 */
	protected $methodName='read';

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

		if( $this->hasKey() )
		{
			$commandArguments[] = $this->getKey();
		}

		/* Execute command */
		$result = $engine->executeCommand( 'defaults', $commandArguments );

		/* Populate output, error */
		$this->didError = $result['exitCode'] !== 0;
		$this->outputString = implode( PHP_EOL, $result['output'] );
	}
}
