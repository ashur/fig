<?php

/*
 * This file is part of Fig
 */
namespace Fig\Exception;

class RuntimeException extends Exception
{
	const FILESYSTEM_PERMISSION_DENIED = 1;
	const FILESYSTEM_NODE_NOT_FOUND = 2;

	const COMMAND_NOT_FOUND = 1024;

	const PROFILE_NOT_FOUND = 4096;
}
