<?php
/*
 * Запрос к API (информация о счете - invoice_info)
 */

include("../library/BitPayments.php");

// Конфигурирование SDK
$sdk = new BitPayments([
    "api_key" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "public_id" => "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz"
]);

try {
    // Запрос на получение информации о счете
    $respnose = $sdk->callAPI("invoice_info", [
        "invoice" => 1
    ]);

    if ($response->success) {
        // ...
    } else {
        echo $response->error;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}