<?php
/*
 * Запрос к API (создание счета - create_invoice)
 */

include("../library/BitPayments.php");

// Конфигурирование SDK
$sdk = new BitPayments([
    "api_key" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "public_id" => "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz"
]);

try {
    // Запрос на создание счета
    $response = $sdk->callAPI("create_invoice", [
        "amount" => 7,
        "currency" => "RUB",
        "account" => 23513,
        "comment" => "Оплата счета №23513"
    ]);

    if ($response->success) {
        // Переадресация на страницу оплату
        header("Location: {$response->message->invoice_link}");
    } else {
        echo $response->error;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}