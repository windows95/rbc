<?php

class Settings {
  protected static ?array $settings = NULL;

  /**
   * @param string $key
   * @return array
   * @throws InvalidArgumentException
   * @throws RuntimeException
   */
  public static function get(string $key): array {
    if (self::$settings === NULL) {
      $path = ROOT_DIR . DIRECTORY_SEPARATOR . 'settings.php';
      if (!file_exists($path) || !is_readable($path)) {
        throw new RuntimeException('Сonfiguration file not found');
      }
      self::$settings = require_once($path);
    }

    if (!array_key_exists($key, self::$settings)) {
      throw new InvalidArgumentException('Unknown configuration key: ' . $key);
    }
    return self::$settings[$key];
  }
}