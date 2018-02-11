<?php

/*
 * This file is part of FigTest
 */
namespace FigTest;

use Cranberry\Shell;

/**
 * A class to facilitate testing Output without writing to disk or stdout
 */
class Output extends Shell\Output\Output
{
	/**
	 * @var	string
	 */
	protected $streamURI='file:///dev/null';

	/**
	 * Returns buffer string
	 *
	 * @return	string
	 */
	public function getBuffer() : string
	{
		return $this->buffer;
	}
}
