<?php

use Cranberry\CLI\Command;

$optionVersion = new Command\ApplicationOption( 'version', 'Display the current version', function()
{
	$this->output->line( sprintf( '%s version %s', $this->app->name, $this->app->version ) );
});

return $optionVersion;
