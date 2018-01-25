<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use FigTest\TestCase;

class VarsTraitTest extends TestCase
{
	/* Helpers */

	public function createObject()
	{
		$mock = $this->getMockForTrait( VarsTrait::class );

		return $mock;
	}


	/* Tests */

	public function test_getVars()
	{
		$object = $this->createObject();

		$vars = [
			'greeting' => getUniqueString( 'hello' ),
			'who' => getUniqueString( 'world' )
		];

		$object->setVars( $vars );

		$this->assertEquals( $vars, $object->getVars() );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function test_setVars_withNonScalarValues_throwsException()
	{
		$object = $this->createObject();

		$invalidVars = [
			'scalar'	=> 'hello world',
			'nonscalar'	=> ['hello', 'world']
		];

		$object->setVars( $invalidVars );
	}
}
