<?php
require "vendor/autoload.php";

use Beeline\BeelineSmsClient;
use Beeline\HttpClientFactory;

echo "<pre>";

$apiClient = new BeelineSmsClient(HttpClientFactory::create('http://web/test/fixtures/sms_send', 'demo', 'demo', []));

echo 'отправка сообщения общим способом';
$result = $apiClient->post_sms('групповое сообщение и ответ в виде массива', ['78125557711', '79065557711']);
var_dump($result);

echo 'отправка сообщения простым запросом';
$result = $apiClient->actionSendSmsByPhone('групповое сообщение и ответ в виде массива', ['78125557711', '79065557711']);
var_dump($result);

echo 'статус сообщения';
$result = $apiClient->status('1');
var_dump($result);

echo 'мультизапрос';
$apiClient->initMultiPost();
$apiClient->actionSendSmsByPhone('групповое сообщение и ответ в виде массива', ['78125557711', '79065557711']);
$apiClient->status('1');
$apiClient->status('2');
$result = $apiClient->processMultiPost();
var_dump($result);


echo 'просто неправильный запрос к api';
$result = $apiClient->apiCall("/?dfsdfsf");
var_dump($result);