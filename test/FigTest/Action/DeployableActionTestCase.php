<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action;

use Fig\Action\AbstractAction;

/**
 * Base test class for all deployable Action classes
 */
abstract class DeployableActionTestCase extends \FigTest\Action\ActionTestCase
{
	/* Helpers */
	abstract public function createObject_fromName( string $name ) : AbstractAction;

	/* Tests */
	abstract public function test_deploy_ignoringErrors( AbstractAction $action );
	abstract public function test_deploy_ignoringOutput( AbstractAction $action );
	abstract public function test_getSubtitle();

	public function test_getName()
	{
		$name = getUniqueString( 'my action ' );
		$action = $this->createObject_fromName( $name );

		$this->assertEquals( $name, $action->getName() );
	}

	public function test_getName_withVariableReplacement()
	{
		$time = time();

		$pattern = 'my action %s';
		$nameString = sprintf( $pattern, '{{ time }}' );
		$expectedName = sprintf( $pattern, $time );

		$action = $this->createObject_fromName( $nameString );
		$action->setVariables( ['time' => $time] );

		$this->assertEquals( $expectedName, $action->getName() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_getType( AbstractAction $action )
	{
		$actionType = $action->getType();

		$this->assertTrue( is_string( $actionType ) );
		$this->assertTrue( strlen( $actionType ) > 0 );
	}

	public function test_isDeployable()
	{
		$name = getUniqueString( 'my action ' );
		$action = $this->createObject_fromName( $name );

		$this->assertTrue( $action->isDeployable() );
	}
}
