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
	 * @param	Huxtable\Core\File\File	$source
	 * @param	Huxtable\Core\File\File	$target
	 * @return	void
	 */
	public function __construct( File\File $source, File\File $target )
	{
		$this->source = $source;
		$this->target = $target;
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
		return [
			'source' => $this->source->getPathname(),
			'target' => $this->target->getPathname()
		];
	}
}
