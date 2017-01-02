<?php
/*
 * Обработчик запросов от BitPayments.Online
 */

include("../library/BitPayments.php");

// Конфигурирование SDK
$sdk = new BitPayments([
    "api_key" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "public_id" => "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz",
    "method" => "POST"
]);

try {
    // Проверка запроса на соответствие всем требованиям и сравнение подписей
    $sdk->handleRequest();

    // Выдача товара/изменение статуса платежа
    // ...

    // Ответ серверу BitPayments.Online об успешной обработке данных счета
    $sdk->handleResult(true);
} catch (Exception $e) {
    $sdk->handleResult(false, $e->getMessage());
}