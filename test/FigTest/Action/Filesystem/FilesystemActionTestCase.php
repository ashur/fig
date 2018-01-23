<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action\Filesystem;

use Cranberry\Filesystem as CranberryFilesystem;
use Fig\Action;
use Fig\Action\AbstractAction;
use Fig\Action\Filesystem\AbstractFileAction;
use Fig\Filesystem;

/**
 * Base test class for all Action\Filesystem classes
 */
abstract class FilesystemActionTestCase extends \FigTest\Action\DeployableActionTestCase
{
	/* Helpers */

	abstract public function createObject_fromTargetPath( string $targetPath ) : AbstractFileAction;

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

	public function test_getTargetPath_withVariableReplacement()
	{
		$filename = getUniqueString( 'file-' );

		$pattern = '/usr/local/foo/%s.txt';
		$targetPath = sprintf( $pattern, '{{ filename }}' );
		$expectedPath = sprintf( $pattern, $filename );

		$action = $this->createObject_fromTargetPath( $targetPath );
		$action->setVariables( ['filename' => $filename ] );

		$this->assertEquals( $expectedPath, $action->getTargetPath() );
	}
}
