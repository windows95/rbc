<?php

abstract class AbsDataFetcher {

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