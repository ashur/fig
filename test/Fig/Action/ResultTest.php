<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use FigTest\TestCase;

class ResultTest extends TestCase
{
	/* Helpers */

	public function createObject_fromDidError( bool $didError ) : Result
	{
		$output = getUniqueString( "line 1\nline 2\n" );
		$result = new Result( $output, $didError );

		return $result;
	}

	public function createObject_fromOutput( string $output ) : Result
	{
		$didError = rand( 0, 1 ) == 0 ? false : true;
		$result = new Result( $output, $didError );

		return $result;
	}


	/* Providers */

	public function provider_didError() : array
	{
		return [
			[true],
			[false],
		];
	}


	/* Tests */

	/**
	 * @dataProvider	provider_didError
	 */
	public function test_didError_alwaysReturnsFalse_whenIgnoringErrors( bool $didError )
	{
		$result = $this->createObject_fromDidError( $didError );
		$result->ignoreErrors( true );

		$this->assertEquals( false, $result->didError() );
	}

	/**
	 * @dataProvider	provider_didError
	 */
	public function test_didError_returnsValue_whenNotIgnoringErrors( bool $didError )
	{
		$result = $this->createObject_fromDidError( $didError );

		$this->assertEquals( $didError, $result->didError() );
	}

	public function test_getOutput_returnsStringOK_whenIgnoringOutput()
	{
		$output = getUniqueString( "line 1\nline 2\n" );
		$result = $this->createObject_fromOutput( $output );

		$result->ignoreOutput( true );

		$this->assertEquals( Result::STRING_STATUS_SUCCESS, $result->getOutput() );
	}

	public function test_getOutput_returnsValue_whenNotIgnoringOutput()
	{
		$output = getUniqueString( "line 1\nline 2\n" );
		$result = $this->createObject_fromOutput( $output );

		$this->assertEquals( $output, $result->getOutput() );
	}
}
