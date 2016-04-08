<?php

/*
 * This file is part of the Fig test suite
 */

use Huxtable\Core\File;

class CommandTest extends PHPUnit_Framework_TestCase
{
	public function testCreation()
	{
		$commandString = 'echo ' . rand( 1, 999 );
		$commandName = str_replace( ' ', '_', $commandString );

		$command = new Fig\Command( $commandName, $commandString );

		$this->assertEquals( $commandString, $command->getCommand() );
		$this->assertEquals( $commandName, $command->getName() );
	}

	public function testExecute()
	{
		$commandString = 'echo ' . rand( 1, 999 );
		$commandName = str_replace( ' ', '_', $commandString );

		$command = new Fig\Command( $commandName, $commandString );

		exec( $commandString, $output, $exitCode );
		$commandResult = $command->exec();

		$this->assertTrue( is_array( $commandResult ) );
		$this->assertEquals( $output, $commandResult['output'] );
		$this->assertEquals( $exitCode, $commandResult['exitCode'] );
	}

	public function testEncode()
	{
		$commandString = 'echo ' . rand( 1, 999 );
		$commandName = str_replace( ' ', '_', $commandString );

		$command = new Fig\Command( $commandName, $commandString );
		$jsonEncoded = json_encode( $command );
		$jsonDecoded = json_decode( $jsonEncoded, true );

		$this->assertEquals( $commandString, $jsonDecoded['command'] );
		$this->assertEquals( $commandName, $jsonDecoded['name'] );
	}
}
