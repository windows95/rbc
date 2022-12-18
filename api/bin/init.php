<?php
// Создание таблиц и заполнение справочника валют
require_once(__DIR__ .  DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php');

$storage = new Storage();
$fetcher = AbsDataFetcher::init(AbsDataFetcher::SOAP);

$storage->init();
$storage->saveCodes($fetcher);