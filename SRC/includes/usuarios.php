<?php

function usuario_email_existe($conexao, string $email): bool
{
    $st = mysqli_prepare($conexao, 'SELECT 1 FROM usuarios WHERE email = ? LIMIT 1');
    mysqli_stmt_bind_param($st, 's', $email);
    mysqli_stmt_execute($st);
    mysqli_stmt_store_result($st);
    $existe = mysqli_stmt_num_rows($st) > 0;
    mysqli_stmt_close($st);
    return $existe;
}

function usuario_cadastrar($conexao, string $nome, string $email, string $senha, int $nivel = 1): bool
{
    $email = mb_strtolower(trim($email), 'UTF-8');

    if (usuario_email_existe($conexao, $email)) {
        return false;
    }

    $hash = hash_senha($email, $senha);

    $st = mysqli_prepare(
        $conexao,
        'INSERT INTO usuarios (nome, email, senha_hash, nivel) VALUES (?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($st, 'sssi', $nome, $email, $hash, $nivel);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}

function usuario_autenticar($conexao, string $email, string $senha): ?array
{
    $email = mb_strtolower(trim($email), 'UTF-8');

    $st = mysqli_prepare(
        $conexao,
        'SELECT id_usuario, nome, senha_hash, nivel FROM usuarios WHERE email = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($st, 's', $email);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $id, $nome, $hashBanco, $nivel);
    $achou = mysqli_stmt_fetch($st);
    mysqli_stmt_close($st);

    if (!$achou || !hash_equals($hashBanco, hash_senha($email, $senha))) {
        return null;
    }

    return ['id_usuario' => $id, 'nome' => $nome, 'nivel' => $nivel];
}