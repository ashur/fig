<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Shell;
use Cranberry\Shell\Input;
use Cranberry\Shell\Output;
use Cranberry\Shell\Middleware;

$___bootstrap = function( Shell\Application &$app )
{
	/*
	 * Middleware
	 */

	/*
	 * Commands
	 */

	/*
	 * Error Middleware
	 */
	$___runtime = function( Input\InputInterface $input, Output\OutputInterface $output, \RuntimeException $exception )
	{
		$output->write( sprintf( '%s: %s', $this->getName(), $exception->getMessage() ) . PHP_EOL );
	};
	$app->pushErrorMiddleware( new Middleware\Middleware( $___runtime, \RuntimeException::class ) );
};
