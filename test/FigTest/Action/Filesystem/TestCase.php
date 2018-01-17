<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action\Filesystem;

use Cranberry\Filesystem as CranberryFilesystem;
use Fig\Action\Filesystem\AbstractFileAction;
use Fig\Filesystem;

abstract class TestCase extends \FigTest\Action\TestCase
{
	/* Helpers */
	abstract public function createActionObject_fromActionName( string $actionName ) : AbstractFileAction;
	abstract public function createActionObject_fromTargetPath( string $targetPath ) : AbstractFileAction;

	/**
	 * Creates mock of given Node subclass
	 *
	 * @param	string	$nodeClass
	 *
	 * @return	Cranberry\Filesystem\Node
	 */
	public function getNodeMock( string $nodeClass ) : CranberryFilesystem\Node
	{
		$nodeMock = $this
			->getMockBuilder( $nodeClass )
			->disableOriginalConstructor()
			->setMethods( ['delete','exists'] )
			->getMock();

		return $nodeMock;
	}


	/* Providers */
	public function provider_NodeClasses() : array
	{
		return [
			[ CranberryFilesystem\File::class ],
			[ CranberryFilesystem\Directory::class ],
			[ CranberryFilesystem\Link::class ],
		];
	}


	/* Tests */
	public function test_getName()
	{
		$actionName = getUniqueString( 'action ' );
		$action = $this->createActionObject_fromActionName( $actionName );

		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getTargetPath_withVariableReplacement()
	{
		$filename = getUniqueString( 'file-' );

		$pattern = '/usr/local/foo/%s.txt';
		$targetPath = sprintf( $pattern, '{{ filename }}' );
		$expectedPath = sprintf( $pattern, $filename );

		$action = $this->createActionObject_fromTargetPath( $targetPath );
		$action->setVariables( ['filename' => $filename ] );

		$this->assertEquals( $expectedPath, $action->getTargetPath() );
	}
}
