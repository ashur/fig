<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
	/**
	 * @param
	 * @return	void
	 */
	public static function createFixturesDirectory()
	{
		$fixturesDirectory = self::getFixturesDirectory();

		if( !$fixturesDirectory->exists() )
		{
			$fixturesDirectory->create();
		}
	}

	/**
	 * Delete fixtures directory
	 */
	public static function deleteFixturesDirectory()
	{
		$directory = self::getFixturesDirectory();

		if( $directory->exists() )
		{
			$files = $directory->children();
			foreach( $files as $file )
			{
				chmod( $file, 0755 );
			}

			$directory->delete();
		}
	}

	/**
	 * @param	string	$name
	 * @return	Cranberry\Core\File\File
	 */
	public static function getFixtureFile( $name )
	{
		$fixturesDirectory = self::getFixturesDirectory();
		$fixtureFile = $fixturesDirectory->child( $name );

		if( !$fixtureFile->exists() )
		{
			$fixtureFile->create();
		}

		return $fixtureFile;
	}

	/**
	 * @return	Cranberry\Core\File\Directory
	 */
	public static function getFixturesDirectory()
	{
		$fixturesPath = dirname( dirname( __DIR__ ) ) . '/fixtures';
		$fixturesDirectory = new \Cranberry\Core\File\Directory( $fixturesPath );

		return $fixturesDirectory;
	}

	/**
	 * Invalid values for `file` value
	 *
	 * @return	array
	 */
	public function invalidFileValueProvider()
	{
		return [
			[ 'string' ],
			[ false ],
			[ 1234 ],
			[ (object)[] ],
		];
	}

	/**
	 * Invalid values for `path` when used with `action: replace_string`
	 *
	 * @return	array
	 */
	public function invalidReplaceStringPathProvider()
	{
		/*
		 * Note: Stubs cannot be passed because this tests string-based paths
		 * usually defined in YAML
		 */
		self::createFixturesDirectory();
		$fixturesDirectory = self::getFixturesDirectory();

		/* Non-existent file */
		$nonExistentFile = $fixturesDirectory->child( time() . '-nonexistent' );

		/* Directory */
		$directoryFile = $fixturesDirectory->childDir( time() . '-profile-directory' );
		$directoryFile->create();

		/* Unreadable file */
		$unreadableFile = $fixturesDirectory->child( time() . '-noread' );
		$unreadableFile->create();
		chmod( $unreadableFile, 0355 );

		/* Unwriteable file */
		$unwriteableFile = $fixturesDirectory->child( time() . '-nowrite' );
		$unwriteableFile->create();
		chmod( $unwriteableFile, 0555 );

		return [
			[ $nonExistentFile->getPathname() ],
			[ $directoryFile->getPathname() ],
			[ $unreadableFile->getPathname() ],
			[ $unwriteableFile->getPathname() ],
		];
	}

	/**
	 * @return	array
	 */
	public function invalidReplaceStringStringValueProvider()
	{
		return [
			[ 'string' ],
			[ false ],
			[ 1234 ],
			[ [] ],
			[ [ 'old' => 'foo' ] ],
			[ [ 'new' => 'bar' ] ],
			[ [ 'old' => 'foo', 'new' => [] ] ],
			[ [ 'old' => [], 'new' => 'bar' ] ],
			[ (object)[] ],
		];
	}

	/**
	 * Non-string values
	 *
	 * @return	array
	 */
	public function invalidStringProvider()
	{
		return [
			[ [] ],
			[ (object)[] ],
			[ true ],
			[ false ],
		];
	}

	/**
	 * Invalid file contents values
	 *
	 * @return	array
	 */
	public function invalidContentsProvider()
	{
		return [
			[ (object)[] ],
		];
	}

	/**
	 * @return	array
	 */
	public function replaceStringOldNewValueProvider()
	{
		return [
			['world', 'everyone', 'hello, world.', 'hello, everyone.'],
			['{{ who }}', 'everyone', 'hello, world.', 'hello, everyone.'],
			['world', '{{ where }}', 'what in the world?', 'what in the heck?'],
			['foo', 'bar', 'hello, world.', 'hello, world.'],
			['o', 'a', 'hello, world.', 'hella, warld.'],
			['o(\W)', 'a\1', 'hello, world.', 'hella, world.'],
			['my (\w+)', '\1 of mine', 'hello, my friend.', 'hello, friend of mine.' ],
		];
	}

	/**
	 * Create fixtures directory
	 */
	public static function setUpBeforeClass()
	{
		self::createFixturesDirectory();
	}

	/**
	 * Delete fixtures directory
	 */
	public static function tearDownAfterClass()
	{
		self::deleteFixturesDirectory();
	}

	/**
	 * @dataProvider		invalidFileValueProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidFileDefinition( $file )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file'] = $file;

		$action = new File( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingFile()
	{
		$properties['name'] = 'foo-' . time();
		$action = new File( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidAction( $action )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = $action;
		$properties['file']['path'] = '~/Desktop';

		$file = new File( $properties );
	}

	/**
	 * @dataProvider		invalidContentsProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidContents( $contents )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'create';
		$properties['file']['path'] = '~/Desktop';
		$properties['file']['contents'] = $contents;

		$file = new File( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidPath( $path )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'create';
		$properties['file']['path'] = $path;

		$file = new File( $properties );
	}

	/**
	 * @dataProvider		invalidStringProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testInvalidSource( $source )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'replace';
		$properties['file']['path'] = '~/Desktop';
		$properties['file']['source'] = $source;

		$file = new File( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingPath()
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'create';

		$file = new File( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testMissingSource()
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'replace';
		$properties['file']['path'] = '~/Desktop';

		$file = new File( $properties );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testReplaceStringRequiresStringProperty()
	{
		$pathFile = self::getFixtureFile( 'testSetValidAction.txt' );
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'replace_string';
		$properties['file']['path'] = $pathFile->getPathname();

		$file = new File( $properties );
	}


	/**
	 * @dataProvider		invalidReplaceStringPathProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testReplaceStringRequiresValidFileDuringDeployment( $path )
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'replace_string';
		$properties['file']['path'] = $path;
		$properties['file']['string'] = ['old' => 'hello', 'new' => 'goodbye'];

		$action = new File( $properties );
		$action->execute();
	}

	/**
	 * @dataProvider		invalidReplaceStringStringValueProvider
	 * @expectedException	InvalidArgumentException
	 */
	public function testReplaceStringStringValueIsArrayWithOldAndNewKeys( $stringValue )
	{
		$pathFile = self::getFixtureFile( 'testSetValidAction.txt' );
		$properties['name'] = 'foo-' . time();

		$properties['file']['action'] = 'replace_string';
		$properties['file']['path'] = $pathFile->getPathname();
		$properties['file']['string'] = $stringValue;

		$file = new File( $properties );
	}

	/**
	 * @dataProvider	replaceStringOldNewValueProvider
	 */
	public function testReplaceStringReplacesMatchingString( $old, $new, $oldContents, $newContents )
	{
		$pathFile = self::getFixtureFile( time() . '-replace-string.txt' );
		$pathFile->putContents( $oldContents );

		$this->assertEquals( $oldContents, $pathFile->getContents() );

		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'replace_string';
		$properties['file']['path'] = $pathFile->getPathname();
		$properties['file']['string'] = ['old' => $old, 'new' => $new];

		$action = new File( $properties );
		$action->setVariables([
			'who' => 'world',
			'where' => 'heck'
		]);

		$action->execute();

		$this->assertEquals( $newContents, $pathFile->getContents() );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testUnsupportedAction()
	{
		$properties['name'] = 'foo-' . time();
		$properties['file']['action'] = 'foo';
		$properties['file']['path'] = '~/Desktop';

		$file = new File( $properties );
	}
}
