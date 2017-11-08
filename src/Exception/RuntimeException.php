<?php

/*
 * This file is part of Fig
 */
namespace Fig\Exception;

class RuntimeException extends Exception
{
	const FILESYSTEM_PERMISSION_DENIED = 1;
	const COMMAND_NOT_FOUND = 2;
}