<?php

define('ROOT_DIR', __DIR__);

spl_autoload_register(function($class) {
  $path = ROOT_DIR . '/classes/';
  require_once  $path . $class .'.php';
});