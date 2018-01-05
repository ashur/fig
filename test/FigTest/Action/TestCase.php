<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action;

abstract class TestCase extends \FigTest\TestCase
{
	abstract public function test_getName();
	abstract public function test_getSubtitle();
	abstract public function test_getType();
}
