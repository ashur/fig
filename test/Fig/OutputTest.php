<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use FigTest\Output as ShellOutput;
use FigTest\TestCase;

class OutputTest extends TestCase
{
	/* Helpers */

	public function createObject( ShellOutput $shellOutput )
	{
		$cols = rand( 40, 300 );
		$useColor = false;

		$output = new Output( $shellOutput, $cols, $useColor );
		return $output;
	}

	public function createObject_fromCols( ShellOutput $shellOutput, int $cols )
	{
		$useColor = false;

		$output = new Output( $shellOutput, $cols, $useColor );
		return $output;
	}

	public function createObject_fromUseColor( ShellOutput $shellOutput, bool $useColor )
	{
		$cols = rand( 40, 300 );

		$output = new Output( $shellOutput, $cols, $useColor );
		return $output;
	}

	/* Providers */



	/* Tests */

	public function test_writeActionHeader()
	{
		$shellOutput = new ShellOutput();
		$output = $this->createObject_fromCols( $shellOutput, 32 );

		$expectedHeader = 'TYPE: subtitle | My Action -----' . PHP_EOL;	// 32 chars
		$output->writeActionHeader( 'type', 'subtitle', 'My Action' );

		$this->assertEquals( $expectedHeader, $shellOutput->getBuffer() );
	}

	public function test_writeActionResult_didError_withColor()
	{
		$shellOutput = new ShellOutput();
		$output = $this->createObject_fromUseColor( $shellOutput, true );

		$outputString = getUniqueString( 'ERROR ' );
		$actionResult = new Action\Result( $outputString, true );

		$output->writeActionResult( $actionResult );

		$expectedOutput = Output::getColorizedString( $outputString, Output::RED ) . PHP_EOL . PHP_EOL;

		$this->assertEquals( $expectedOutput, $shellOutput->getBuffer() );
	}

	public function test_writeActionResult_didNotError_withColor()
	{
		$shellOutput = new ShellOutput();
		$output = $this->createObject_fromUseColor( $shellOutput, true );

		$outputString = getUniqueString( 'OK ' );
		$actionResult = new Action\Result( $outputString, false );

		$output->writeActionResult( $actionResult );

		$expectedOutput = Output::getColorizedString( $outputString, Output::GREEN ) . PHP_EOL . PHP_EOL;

		$this->assertEquals( $expectedOutput, $shellOutput->getBuffer() );
	}

	public function test_writeActionResult_withoutColor()
	{
		$shellOutput = new ShellOutput();
		$output = $this->createObject_fromUseColor( $shellOutput, false );

		$outputString = getUniqueString( 'OK ' );
		$actionResult = new Action\Result( $outputString, false );

		$output->writeActionResult( $actionResult );

		$expectedOutput = $outputString . PHP_EOL . PHP_EOL;

		$this->assertEquals( $expectedOutput, $shellOutput->getBuffer() );
	}

	public function test_getCols()
	{
		$shellOutput = new ShellOutput();
		$cols = rand( 40, 300 );
		$output = $this->createObject_fromCols( $shellOutput, $cols );

		$this->assertEquals( $cols, $output->getCols() );
	}
}
