<?php

class SoapFetcher extends AbsDataFetcher {

  protected const WSDL = 'https://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?WSDL';

  protected SoapClient $client;

  public function __construct() {
    $this->client = new SoapClient(self::WSDL, ['soap_version' => SOAP_1_2, 'exceptions' => TRUE]);
  }

  /**
   * @return array
   */
  public function getCodes(): array {
    $response = $this->client->EnumValutesXML()->EnumValutesXMLResult->any;

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($response);

    $xpath = new DOMXPath($dom);
    $codes = [];
    foreach ($xpath->query('/ValuteData/EnumValutes') as $node) {
      $code = $xpath->query('./VcharCode', $node)->item(0);
      $name = $xpath->query('./Vname', $node)->item(0);

      if (!$code instanceof DOMElement || !$name instanceof DOMElement) {
        continue;
      }
      $codes[trim($code->nodeValue)] = trim($name->nodeValue);
    }
    return $codes;
  }

  /**
   * @return DateTime
   */
  public function getLatestDate(): DateTime {
    $date = $this->client->GetLatestDateTime();
    return DateTime::createFromFormat('Y-m-d\T00:00:00', $date->GetLatestDateTimeResult);
  }

  /**
   * @param DateTime $date
   * @return array
   */
  public function getRates(DateTime $date): array {
    $response = $this->client->GetCursOnDateXML(['On_date' => $date->format('Y-m-d\T00:00:00')])->GetCursOnDateXMLResult->any;

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($response);

    $xpath = new DOMXPath($dom);
    $rates = [];
    foreach ($xpath->query('/ValuteData/ValuteCursOnDate') as $node) {
      $code = $xpath->query('./VchCode', $node)->item(0);
      $rate = $xpath->query('./Vcurs', $node)->item(0);

      if (!$code instanceof DOMElement || !$rate instanceof DOMElement) {
        continue;
      }
      $rates[$code->nodeValue] = $rate->nodeValue;
    }
    return $rates;
  }
}