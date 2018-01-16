<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action\Shell;

use Fig\Action\AbstractAction;
use Fig\Shell;

abstract class TestCase extends \FigTest\Action\TestCase
{
	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_deploy_invalidCommand_causesError( AbstractAction $action )
	{
		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( false );

		$action->deploy( $shellMock );

		$this->assertTrue( $action->didError() );
	}
}
