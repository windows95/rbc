<?php

define('ROOT_DIR', __DIR__);

spl_autoload_register(function(string $class): void {
  $path = ROOT_DIR . '/classes/';
  require_once  $path . $class .'.php';
});