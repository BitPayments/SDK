<?php
/*
 * Получение HTML формы с кнопкой для перехода на страницу оплаты
 */

include("../library/BitPayments.php");

// Конфигурирование SDK
$sdk = new BitPayments([
    "api_key" => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "public_id" => "zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz"
]);

try {
    // Получение HTML формы
    $html = $sdk->getInvoiceHtmlForm([
        "amount" => 7,
        "currency" => "RUB",
        "account" => "nickname",
        "comment" => "Пополнение баланса пользователя nickname"
    ]);

    // Вывод HTML формы
    echo $html;
} catch (Exception $e) {
    echo $e->getMessage();
}