<?php

/*
 * This file is part of Fig
 */
namespace Fig\Shell;

use FigTest\TestCase;

class ResultTest extends TestCase
{
	public function test_getExitCode()
	{
		$exitCode = rand( 0, 128 );
		$result = new Result( [], $exitCode );

		$this->assertEquals( $exitCode, $result->getExitCode() );
	}

	public function test_getOutput()
	{
		$output = [];
		$outputLines = rand( 0, 5 );

		for( $i = 0; $i < $outputLines; $i++ )
		{
			$output[] = getUniqueString( "{$i}-" );
		}

		$result = new Result( $output, 0 );

		$this->assertEquals( $output, $result->getOutput() );
	}
}
