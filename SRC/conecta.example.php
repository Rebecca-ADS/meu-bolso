<?php

mysqli_report(MYSQLI_REPORT_OFF);

$conexao = mysqli_connect('localhost', 'seu_usuario', 'sua_senha', 'nome_do_banco');

if (!$conexao) {
    error_log('Erro de conexão: ' . mysqli_connect_error());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}

mysqli_set_charset($conexao, 'utf8mb4');