<?php

/*
 * This file is part of Fig
 */
namespace Fig\Exception;

class ProfileSyntaxException extends Exception
{
	const MISSING_REQUIRED_PROPERTY = 1;
	const RECURSION = 2;
}
