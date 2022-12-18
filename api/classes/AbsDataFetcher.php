<?php

abstract class AbsDataFetcher {

  public const SOAP = 'soap';

  /**
   * @param string $type
   * @throws InvalidArgumentException
   */
  public static function init(string $type) {
    switch ($type) {
      case self::SOAP:
        return new SoapFetcher();
      default:
        throw new InvalidArgumentException('Wrong fetcher type: ' . $type);
    }
  }

  /**
   * @return array
   */
  abstract public function getCodes(): array;

  /**
   * @return DateTime
   */
  abstract public function getLatestDate(): DateTime;

  /**
   * @param DateTime $date
   * @return array
   */
  abstract public function getRates(DateTime $date): array;
}