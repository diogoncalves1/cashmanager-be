<?php
// Criar o socket (IPv4, TCP)
$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($server === false) {
    echo "Erro ao criar socket: " . socket_strerror(socket_last_error()) . "\n";
    exit();
}

// Definir a porta e o endereço de escuta
$host = '127.0.0.1'; // Endereço local
$port = 8080;         // Porta do servidor

// Associar o socket a um endereço e porta
if (socket_bind($server, $host, $port) === false) {
    echo "Erro ao associar o socket: " . socket_strerror(socket_last_error($server)) . "\n";
    exit();
}

// Ouvir por conexões
if (socket_listen($server, 5) === false) {
    echo "Erro ao ouvir a porta: " . socket_strerror(socket_last_error($server)) . "\n";
    exit();
}

echo "Aguardando conexões em $host:$port...\n";

// Aceitar uma conexão
$client = socket_accept($server);
if ($client === false) {
    echo "Erro ao aceitar conexão: " . socket_strerror(socket_last_error($server)) . "\n";
    exit();
}

// Ler dados do cliente
$msg = socket_read($client, 1024);
echo "Mensagem recebida do cliente: $msg\n";

// Enviar uma resposta para o cliente
$response = "Olá do servidor!";
socket_write($client, $response, strlen($response));

// Fechar as conexões
socket_close($client);
socket_close($server);
