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
	 * Returns buffer string
	 *
	 * @return	string
	 */
	public function getBuffer() : string
	{
		return $this->buffer;
	}

	/**
	 * Redirect to buffer for testing
	 *
	 * @param	string	$string
	 *
	 * @return	void
	 */
	public function write( string $string )
	{
		$this->buffer( $string );
	}
}
