<?php

class Storage {
  protected PDO $connection;

  public function __construct() {
    $config = Settings::get('db');

    $host = $config['host'];
    $db = $config['database'];
    $user = $config['user'];
    $pass = $config['pass'];

    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ];

    $this->connection = new PDO($dsn, $user, $pass, $options);
  }

  /**
   * @param AbsDataFetcher
   */
  public function saveCodes(AbsDataFetcher $fetcher): void {
    $stmt = $this->connection->prepare('INSERT INTO currency (code, name) VALUES(?, ?)');
    foreach ($fetcher->getCodes() as $code => $name) {
      $stmt->execute([$code, $name]);
    }
  }

  /**
   * @return array
   */
  public function getCodes(): array {
    $stmt = $this->connection->query('SELECT code, name FROM currency');
    $codes = [];
    foreach ($stmt->fetchAll() as $currency) {
      $codes[$currency->code] = $currency->name;
    }
    ksort($codes);
    return $codes;
  }

  /**
   * @param AbsDataFetcher $fetcher
   * @param DateTime $date
   */
  public function saveRates(AbsDataFetcher $fetcher, DateTime $date): void {
    $stmt = $this->connection->prepare('INSERT INTO rate (code, day, rate) VALUE (:code, :day, :rate)');
    $day = $date->format('Y-m-d');
    foreach ($fetcher->getRates($date) as $code => $rate) {
      $rate = (int) bcmul($rate, '100', 2);

      $stmt->bindParam('code', $code);
      $stmt->bindParam('day', $day);
      $stmt->bindParam('rate', $rate, PDO::PARAM_INT);
      $stmt->execute();
    }
  }

  /**
   * @param string $code
   * @param DateTime $date
   * @throws RuntimeException
   * @throws InvalidArgumentException
   * @return string|NULL
   */
  public function getRate(string $code, DateTime $date): ?string {
    if (!array_key_exists($code, $this->getCodes())) {
      throw new InvalidArgumentException('Неизвестный код валюты');
    }

    $stmt = $this->connection->prepare('SELECT rate FROM rate WHERE code = :code AND day = :date');
    $stmt->execute(['code' => $code, 'date' => $date->format('Y-m-d')]);
    $rate = $stmt->fetchColumn(0);
    if ($rate === FALSE) {
      return NULL;
    }
    return bcdiv($rate, '100', 2);
  }

  public function init(): void {
    $sql = "
      CREATE TABLE IF NOT EXISTS currency(
        code VARCHAR(3) NOT NULL,
        name VARCHAR(200) NOT NULL,
        PRIMARY KEY(code)
      )
    ";

    $this->connection->prepare($sql)->execute();

    $sql = "
      CREATE TABLE IF NOT EXISTS rate(
        code VARCHAR(3) NOT NULL,
        day DATE NOT NULL,
        rate INT(5) UNSIGNED NOT NULL,
        PRIMARY KEY(code, day),
        CONSTRAINT fk_rate_2_currency
          FOREIGN KEY (code) REFERENCES currency (code)
      )
    ";

    $this->connection->prepare($sql)->execute();
  }
}