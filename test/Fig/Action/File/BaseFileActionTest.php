<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\File;

use Fig\Engine;
use FigTest\Action\TestCase;

class BaseFileActionTest extends TestCase
{
	/* Consumed by FigTest\Action\TestCase::test_getType */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$action = new ExampleFileAction( $actionName, '~/Desktop/' );

		return [
			[$action]
		];
	}

	public function test_getTargetPath_withVariableReplacement()
	{
		$filename = microtime( true );

		$pattern = '~/Desktop/%s.txt';
		$targetPath = sprintf( $pattern, '{{ filename }}' );
		$expectedPath = sprintf( $pattern, $filename );

		$action = new ExampleFileAction( 'My Example Action', $targetPath );
		$action->setVariables( ['filename' => $filename ] );

		$this->assertEquals( $expectedPath, $action->getTargetPath() );
	}

	public function test_getName()
	{
		$actionName = getUniqueString( 'action ' );
		$action = new ExampleFileAction( $actionName, '~/Desktop/' );

		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getSubtitle()
	{
		$action = new ExampleFileAction( 'My Example Action', '~/Desktop' );

		$this->assertEquals( 'example', $action->getSubtitle() );
	}
}

class ExampleFileAction extends BaseFileAction
{
	/**
	 * @var	string
	 */
	protected $subtitle = 'example';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$targetPath
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $targetPath )
	{
		$this->name = $name;

		$this->targetPath = $targetPath;
	}

	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine ){}
}
