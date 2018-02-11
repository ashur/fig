<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Shell\Output as ShellOutput;

class Output
{
	const CHAR_ERROR = '*';
	const CHAR_HEADER = '-';
	const STRING_ACTION_TITLE = '%s: %s | %s ';

	const RED   = '0;31';
	const GREEN = '0;32';

	/**
	 * @var	int
	 */
	protected $cols;

	/**
	 * @var	Cranberry\Shell\Output\OutputInterface
	 */
	protected $output;

	/**
	 * @var	bool
	 */
	protected $useColor;

	/**
	 * @param	Cranberry\Shell\Output\OutputInterface	$output
	 *
	 * @param	int	$cols
	 *
	 * @param	bool	$useColor
	 *
	 * @return	void
	 */
	public function __construct( ShellOutput\OutputInterface &$output, int $cols, bool $useColor )
	{
		$this->output = $output;
		$this->cols = $cols;
		$this->useColor = $useColor;
	}

	/**
	 * Returns string formatted with ANSI color
	 *
	 * @param	string	$string
	 *
	 * @param	string	$color
	 *
	 * @return	string
	 */
	static public function getColorizedString( string $string, string $color ) : string
	{
		$colorizedString = sprintf( "\033[%sm%s\033[0m", $color, $string );
		return $colorizedString;
	}

	/**
	 * Returns the number of columns available for output
	 *
	 * @return	int
	 */
	public function getCols() : int
	{
		return $this->cols;
	}

	/**
	 * Writes action header to output
	 *
	 * @param	string	$type	Action type (ex., 'Command')
	 *
	 * @param	string	$subtitle	Action subtitle
	 *
	 * @param	string	$name	Action name
	 *
	 * @return	void
	 */
	public function writeActionHeader( string $type, string $subtitle, string $name )
	{
		$title = sprintf( '%s: %s | %s ', strtoupper( $type ), $subtitle, $name );

		$padChar = self::CHAR_HEADER;
		$header = sprintf( "%'{$padChar}-{$this->cols}s", $title );

		$this->output->write( $header . PHP_EOL );
	}

	/**
	 * Writes action output contents to output.
	 *
	 * If using color, output string in green for success or red for failure
	 *
	 * @param	Fig\Action\Result	$result
	 *
	 * @return	void
	 */
	public function writeActionResult( Action\Result $result )
	{
		if( $this->useColor )
		{
			$color = $result->didError() ? self::RED : self::GREEN;
			$output = self::getColorizedString( $result->getOutput(), $color );
		}
		else
		{
			$output = $result->getOutput();
		}

		$this->output->write( $output . PHP_EOL . PHP_EOL );
	}

	/**
	 * Writes halting deployment error to output.
	 *
	 * If using color, output string in red
	 *
	 * @return	void
	 */
	public function writeHaltingDeployment()
	{
		$padChar = self::CHAR_ERROR;
		$output = sprintf( "%'{$padChar}{$this->cols}s", ' HALTING DEPLOYMENT' );

		if( $this->useColor )
		{
			$output = self::getColorizedString( $output, self::RED );
		}

		$this->output->write( $output . PHP_EOL . PHP_EOL );
	}
}
