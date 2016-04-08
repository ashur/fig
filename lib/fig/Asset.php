<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Huxtable\Core\File;

class Asset implements \JsonSerializable
{
	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $source;

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $target;

	/**
	 * @param	Huxtable\Core\File\File|Directory	$source
	 * @param	Huxtable\Core\File\File|Directory	$target
	 * @return	void
	 */
	public function __construct( File\File $source, File\File $target )
	{
		$this->source = $source;
		$this->target = $target;
	}

	/**
	 * @param	string	$json
	 * @return	self
	 */
	static public function getInstanceFromJSON( $json )
	{
		$data = json_decode( $json, true );

		$expectedStructure = [
			'source' => ['path', 'type'],
			'target' => ['path', 'type'],
		];

		foreach( $expectedStructure as $key => $values )
		{
			foreach( $values as $value )
			{
				if( !isset( $data[$key][$value] ) )
				{
					throw new \Exception( 'Malformed asset data encountered' );
				}
			}
		}

		switch( strtolower( $data['source']['type'] ) )
		{
			case 'file':
				$fileSource = new File\File( $data['source']['path'] );
				break;

			case 'folder':
				$fileSource = new File\Directory( $data['source']['path'] );
				break;
		}

		switch( strtolower( $data['target']['type'] ) )
		{
			case 'file':
				$fileTarget = new File\File( $data['target']['path'] );
				break;

			case 'folder':
				$fileTarget = new File\Directory( $data['target']['path'] );
				break;
		}

		return new self( $fileSource, $fileTarget );
	}

	/**
	 * @return	Huxtable\Core\File\File|Directory
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @return	Huxtable\Core\File\File|Directory
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		$sourceType = $this->source instanceof File\Directory ? 'folder' : 'file';
		$targetType = $this->target instanceof File\Directory ? 'folder' : 'file';

		return [
			'source' => [
				'path' => $this->source->getPathname(),
				'type' => $sourceType
			],
			'target' => [
				'path' => $this->target->getPathname(),
				'type' => $targetType
			]
		];
	}
}
