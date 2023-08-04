<?php
error_reporting(0);

$botToken = 'SEU_TOKEN_DO_TELEGRAM'; // BOT DO TELEGRAM
$webhookUrl = 'URL_DO_SEU_SITE'; // WEBOOK DO SEU BOT DO TELEGRAM


function setWebhook($botToken, $webhookUrl)
{
    $apiUrl = "https://api.telegram.org/bot$botToken/setWebhook";
    $postData = [
        'url' => $webhookUrl,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Definindo o webhook ao iniciar o script

setWebhook($botToken, $webhookUrl);

// Função para consultar o CEP usando a API do ViaCEP

function consultaCEP($cep)
{
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);
$message = $update['message'];
$chatId = $message['chat']['id'];
$command = $message['text'];

// Verificando o comando /cep

if ($command == '/cep') {
    $reply = "Digite o CEP que você deseja consultar.";
} elseif (strpos($command, '/cep ') === 0) {
    $cep = trim(substr($command, 5));
    $response = consultaCEP($cep);
    $data = json_decode($response, true);
    if (isset($data['cep'])) {
        $reply = "CEP: {$data['cep']}\n";
        $reply .= "Logradouro: {$data['logradouro']}\n";
        $reply .= "Complemento: {$data['complemento']}\n";
        $reply .= "Bairro: {$data['bairro']}\n";
        $reply .= "Cidade: {$data['localidade']}\n";
        $reply .= "Estado: {$data['uf']}\n";
    } else {
        $reply = "CEP inválido. Por favor, digite um CEP válido.";
    }
} else {
    $reply = "Comando inválido. Use /cep para consultar um CEP.";
}

// Enviando a resposta para o Telegram

$apiUrl = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($reply);
file_get_contents($apiUrl);