<?php

require_once('bootstrap.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$action = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = strtolower($_SERVER['REQUEST_METHOD']);

try {
  if ($method !== 'get') {
    throw new InvalidArgumentException('Разрешен только метод GET');
  }
  if (!in_array($action, ['last-date', 'rate', 'codes'])) {
    throw new InvalidArgumentException('Несуществующий метод API');
  }
  if ($request === NULL) {
    throw new InvalidArgumentException('Должны быть указаны обязательные параметры: date, code');
  }

  $params = [];
  parse_str($request, $params);

  $api = new Api($params);
  switch ($action) {
    case 'last-date':
      $api->lastDate();
      break;
    case 'codes':
      $api->codes();
      break;
    case 'rate':
      $api->rate();
      break;
  }
} catch (InvalidArgumentException $e) {
  http_response_code(400);
  echo json_encode(['error' => $e->getMessage()]);
} catch (Throwable $e) {
  http_response_code(500);
  // echo json_encode(['error' => $e->getMessage()]);
}