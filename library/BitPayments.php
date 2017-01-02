<?php

class BitPayments {
    // Структура поддерживаемых данных
    public $structure = [
        // URL адрес API
        "api_url" => "https://bitpayments.online/api/interface",
        "form_url" => "https://bitpayments.online/payment",
        // Версия API
        "api_version" => "v1",
        // Поддерживаемые валюты
        "currencies" => [
            "BTC",
            "USD",
            "RUB"
        ],
        // Поддерживаемые типы подписей
        "signatures" => [
            "form",
            "handler"
        ],
        // Поддерживаемые методы API
        "apimethods" => [
            "create_invoice",
            "invoice_info"
        ],
        // Требуемые API методами параметры
        "apiparameters" => [
            "create_invoice" => [
                "amount",
                "currency",
                "account",
                "comment"
            ],
            "invoice_info" => [
                "invoice"
            ]
        ],
        // Требуемые обработчику параметры
        "handlerparameters" => [
            "amount",
            "account",
            "comment",
            "signature"
        ],
        // IP адреса серверов BitPayments.Online
        "gatewayips" => [
            "79.137.37.234"
        ]
    ];
    
    // Конфигурационная переменная
    public $configuration;
    
    public function __construct(array $parameters) {
        if (empty($parameters['api_key']) ||
            empty($parameters['public_id'])) { 
            throw new InvalidArgumentException("Не все поля заполнены");
        }
        
        if (strlen($parameters['api_key']) != 32) {
            throw new UnexpectedValueException("Поле api_key содержит неверное значение!");
        }
        
        if (strlen($parameters['public_id']) != 32) {
            throw new UnexpectedValueException("Поле public_id содержит неверное значение!");
        }
        
        if (!empty($parameters['method'])) { 
            if (!in_array($parameters['method'], ["GET", "POST"])) {
                throw new UnexpectedValueException("Поле method содержит неверное значение!");
            }
        }
        
        $this->configuration = $parameters;
    }
    
    public function getSignature($type, array $data) {
        if (!in_array($type, $this->structure['signatures'])) {
            throw new InvalidArgumentException("Неверный тип подписи!");
        }
        
        $signature = "";
        
        switch($type) {
            case "form":
                $signature = hash("sha256", "{$data['amount']}:{$data['currency']}:{$data['account']}:{$data['comment']}:{$this->configuration['api_key']}");
            break;
            
            case "handler":
                $signature = hash("sha256", "{$data['amount']['BTC']}:{$data['account']}:{$data['comment']}:{$this->configuration['api_key']}");
            break;
        }
        
        return $signature;
    }
    
    // Функция создания счета без API
    public function getInvoiceHtmlForm(array $parameters) {
        foreach($this->structure['apiparameters']['create_invoice'] as $parameter) {
            if (!isset($parameters[$parameter])) {
                throw new InvalidArgumentException("Параметр {$parameter} не заполнен!");
            }
        }
        
        if (empty($parameters['signature'])) {
            $parameters['signature'] = $this->getSignature("form", $parameters);
        }
        $parameters['public_id'] = $this->configuration['public_id'];
        
        $inputs = "";
        
        foreach($parameters as $parameter => $value) {
            $inputs .= "<input type='hidden' name='{$parameter}' value='{$value}'/>\n";
        }
        
        $html = "<form action='{$this->structure['form_url']}' method='POST'>
            {$inputs}
            <input type='submit' value='Оплатить'/>
        </form>";
        
        return $html;
    }
    
    // Функции обработчика
    public function handleRequest() {
        if (!isset($this->configuration['method'])) {
            throw new InvalidArgumentException("Метод запросов на обработчик не указан");
        }
        
        if (!in_array($_SERVER['REMOTE_ADDR'], $this->structure['gatewayips'])) {
            throw new InvalidArgumentException("Неверный IP адрес");
        }
        
        if ($_SERVER['REQUEST_METHOD'] != $this->configuration['method']) {
            throw new InvalidArgumentException("Неверный тип запроса");
        }
        
        $data = [];
        switch($_SERVER['REQUEST_METHOD']) {
            case "GET": $data = $_GET; break;
            case "POST": $data = $_POST; break;
        }

	if (!isset($data['data'])) {
	    throw new InvalidArgumentException("Отсутствуют данные");
	}

	$data = json_decode($data['data'], true);
        
        foreach($this->structure['handlerparameters'] as $parameter) {
            if (!isset($data[$parameter])) {
                throw new InvalidArgumentException("Параметр {$parameter} не заполнен!");
            }
        }
        
        if ($data['signature'] != $this->getSignature("handler", $data)) {
            throw new InvalidArgumentException("Неверная подпись счета!");
        }
    }
    
    public function handleResult($success, $message = null) {
        header('Content-Type: application/json');
        
        echo json_encode([
            "success" => $success,
            "message" => $message
        ]);
    }
    
    // Функция API
    public function callAPI($method, array $parameters) {
        if (!in_array($method, $this->structure['apimethods'])) {
            throw new InvalidArgumentException("Неверный метод API");
        }
        
        foreach($this->structure['apiparameters'][$method] as $parameter) {
            if (!isset($parameters[$parameter])) {
                throw new InvalidArgumentException("Параметр {$parameter} не заполнен!");
            }
        }
        
        $url = "{$this->structure['api_url']}/{$this->structure['api_version']}/{$method}/{$this->configuration['api_key']}/?" . http_build_query($parameters, null, '&', PHP_QUERY_RFC3986);
        $result = json_decode(file_get_contents($url));
        
        if (!is_object($result)) {
            throw new InvalidArgumentException("Ошибка сервера BitPayments! Повторите запрос позже");
        }
        
        return $result;
    }
}