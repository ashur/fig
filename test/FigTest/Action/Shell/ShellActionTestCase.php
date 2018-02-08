<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action\Shell;

use Fig\Action;
use Fig\Action\AbstractAction;
use Fig\Shell;
use FigTest\Action\DeployableActionTestCase;

/**
 * Base test class for all Action\Shell classes
 */
abstract class ShellActionTestCase extends DeployableActionTestCase
{
	/* Tests */

	public function test_deployWithShell()
	{
		$action = $this->createObject();
		
		$this->assertTrue( method_exists( $action, 'deployWithShell' ) );
	}	

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_ignoringErrors( AbstractAction $action )
	{
		$commandOutput = getUniqueString( 'error ' );

		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( true );
		$shellMock
			->method( 'executeCommand' )
			->willReturn( new Shell\Result( [$commandOutput], 1 ) );

		$action->ignoreErrors( true );
		$result = $action->deployWithShell( $shellMock );

		$this->assertFalse( $result->didError() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_ignoringOutput( AbstractAction $action )
	{
		$commandOutput = getUniqueString( 'output ' );

		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( true );
		$shellMock
			->method( 'executeCommand' )
			->willReturn( new Shell\Result( [$commandOutput], 0 ) );

		$action->ignoreOutput( true );
		$result = $action->deployWithShell( $shellMock );

		$this->assertEquals( Action\Result::STRING_STATUS_SUCCESS, $result->getOutput() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_commandError_causesError( AbstractAction $action )
	{
		$commandOutput = getUniqueString( 'error ' );

		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( true );
		$shellMock
			->method( 'executeCommand' )
			->willReturn( new Shell\Result( [$commandOutput], 1 ) );

		$result = $action->deployWithShell( $shellMock );

		$this->assertTrue( $result->didError() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_invalidCommand_causesError( AbstractAction $action )
	{
		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( false );
		$shellMock
			->expects( $this->never() )
			->method( 'executeCommand' );

		$result = $action->deployWithShell( $shellMock );

		$this->assertTrue( $result->didError() );

		$expectedOutputPrefix = sprintf( Shell\Shell::STRING_ERROR_COMMANDNOTFOUND, '' );
		$this->assertStringStartsWith( $expectedOutputPrefix, $result->getOutput() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_invalidCommand_ignoringErrors( AbstractAction $action )
	{
		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( false );
		$shellMock
			->expects( $this->never() )
			->method( 'executeCommand' );

		$action->ignoreErrors( true );
		$result = $action->deployWithShell( $shellMock );

		$this->assertTrue( $result->didError() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_invalidCommand_ignoringOutput( AbstractAction $action )
	{
		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( false );
		$shellMock
			->expects( $this->never() )
			->method( 'executeCommand' );

		$action->ignoreOutput( true );
		$result = $action->deployWithShell( $shellMock );

		$expectedOutputPrefix = sprintf( Shell\Shell::STRING_ERROR_COMMANDNOTFOUND, '' );
		$this->assertStringStartsWith( $expectedOutputPrefix, $result->getOutput() );
	}
}
