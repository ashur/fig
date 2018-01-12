<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action;

abstract class TestCase extends \FigTest\TestCase
{
	/* Providers */
	abstract public function provider_ActionObject() : array;

	/* Tests */
	abstract public function test_getName();
	abstract public function test_getSubtitle();

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_getType( \Fig\Action\Action $action )
	{
		$actionType = $action->getType();

		$this->assertTrue( is_string( $actionType ) );
		$this->assertTrue( strlen( $actionType ) > 0 );
	}
}
