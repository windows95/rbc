<?php

class Api {
  protected array $params;

  /**
   * @param array $data
   * @param int $httpCode
   */
  protected function response(array $data, int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode($data);
  }

  /**
   * @param array $params
   */
  public function __construct(array $params) {
    $this->params = $params;
  }

  /**
   * @return array
   * @throws InvalidArgumentException
   */
  protected function validate(): array {
    if (array_diff_key(array_flip(['date', 'code']), $this->params)) {
      throw new InvalidArgumentException('Должны быть указаны обязательные параметры: date, code');
    }

    $codeRe = '/^[A-Z]{3}$/';

    if (!preg_match($codeRe, $this->params['code'])) {
      throw new InvalidArgumentException('Неправильный код валюты');
    }

    $base = NULL;
    if (array_key_exists('base', $this->params)) {
      if (preg_match($codeRe, $this->params['base'])) {
        $base = $this->params['base'];
      } else {
        throw new InvalidArgumentException('Неправильный код базовой валюты');
      }
    }

    $code = $this->params['code'];
    $date = DateTime::createFromFormat('Y-m-d', $this->params['date']);

    if ($date === FALSE) {
      throw new InvalidArgumentException('Формат даты: YYY-MM-DD');
    }
    return [$date, $code, $base];
  }

  public function codes(): void {
    try {
      $storage = new Storage();
      $this->response($storage->getCodes());
    } catch (Throwable $e) {
      $this->response([], 500);
    }
  }

  public function lastDate(): void {
    try {
      $fetcher = new SoapFetcher();
      $this->response(['date' => $fetcher->getLatestDate()->format('d.m.Y')]);
    } catch (Throwable $e) {
      $this->response([], 500);
    }
  }

  public function rate(): void {
    try {
      [$date, $code, $base] = $this->validate();

      $rate = $this->getRate($code, $date);
      if ($rate === NULL) {
        throw new InvalidArgumentException('Нет данных для выбранной даты');
      }

      $prevDate = clone($date);
      $prevDate->modify('-1 day');

      $prevRate = $this->getRate($code, $prevDate);

      $this->response(['rate' => $rate, 'prev' => $prevRate]);
    } catch (InvalidArgumentException $e) {
      $this->response(['error' => $e->getMessage(), 400]);
    } catch (Throwable $e) {
      $this->response([], 500);
    }
  }

  /**
   * @param string $code
   * @param DateTime $date
   * @return string|NULL
   */
  protected function getRate(string $code, DateTime $date): ?string {
    $fetcher = new SoapFetcher();
    if ($date > $fetcher->getLatestDate()) {
      return NULL;
    }

    $storage = new Storage();
    $rate = $storage->getRate($code, $date);
    // Получение данных из cbr.ru
    if ($rate === NULL) {
      $storage->saveRates($fetcher, $date);
    }
    $rate = $storage->getRate($code, $date);
    if ($rate === NULL) {
      return NULL;
    }
    return $rate;
  }
}