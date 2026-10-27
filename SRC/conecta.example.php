<?php

declare(strict_types=1);

$dbHost = 'localhost';
$dbPorta = '3306';
$dbNome = 'meu_bolso';
$dbUsuario = 'seu_usuario';
$dbSenha = '';

$dsn = "mysql:host=$dbHost;port=$dbPorta;dbname=$dbNome;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUsuario, $dbSenha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Erro na conexão com o banco: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}

unset($dbHost, $dbPorta, $dbNome, $dbUsuario, $dbSenha, $dsn);
