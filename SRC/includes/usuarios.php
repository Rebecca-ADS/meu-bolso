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

// Dados de um usuário (sem a senha)
function usuario_buscar($conexao, int $idUsuario): ?array
{
    $st = mysqli_prepare(
        $conexao,
        'SELECT id_usuario, nome, email, nivel FROM usuarios WHERE id_usuario = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($st, 'i', $idUsuario);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $id, $nome, $email, $nivel);
    $achou = mysqli_stmt_fetch($st);
    mysqli_stmt_close($st);

    if (!$achou) {
        return null;
    }
    return ['id_usuario' => $id, 'nome' => $nome, 'email' => $email, 'nivel' => $nivel];
}

function usuario_atualizar_nome($conexao, int $idUsuario, string $nome): bool
{
    $st = mysqli_prepare($conexao, 'UPDATE usuarios SET nome = ? WHERE id_usuario = ?');
    mysqli_stmt_bind_param($st, 'si', $nome, $idUsuario);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}

// Grava o novo hash (o salt é o e-mail/login do próprio usuário)
function usuario_trocar_senha($conexao, int $idUsuario, string $email, string $novaSenha): bool
{
    $hash = hash_senha($email, $novaSenha);

    $st = mysqli_prepare($conexao, 'UPDATE usuarios SET senha_hash = ? WHERE id_usuario = ?');
    mysqli_stmt_bind_param($st, 'si', $hash, $idUsuario);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}

// Lista de usuários com ordenação (nomes de coluna vêm de uma lista fixa)
function usuario_listar($conexao, string $ordem = 'nome', string $dir = 'asc'): array
{
    $colunas = [
        'id'    => 'id_usuario',
        'nome'  => 'nome',
        'email' => 'email',
        'nivel' => 'nivel',
    ];
    $campo = $colunas[$ordem] ?? $colunas['nome'];
    $sentido = ($dir === 'desc') ? 'DESC' : 'ASC';

    $res = mysqli_query(
        $conexao,
        'SELECT id_usuario, nome, email, nivel FROM usuarios ORDER BY ' . $campo . ' ' . $sentido . ', id_usuario ASC'
    );

    $lista = [];
    while ($linha = mysqli_fetch_assoc($res)) {
        $lista[] = $linha;
    }
    return $lista;
}

function usuario_alterar_nivel($conexao, int $idUsuario, int $nivel): bool
{
    $st = mysqli_prepare($conexao, 'UPDATE usuarios SET nivel = ? WHERE id_usuario = ?');
    mysqli_stmt_bind_param($st, 'ii', $nivel, $idUsuario);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}

// Falha (false) se o usuário possuir dados ligados a ele no banco
function usuario_excluir($conexao, int $idUsuario): bool
{
    $st = mysqli_prepare($conexao, 'DELETE FROM usuarios WHERE id_usuario = ?');
    mysqli_stmt_bind_param($st, 'i', $idUsuario);
    $ok = mysqli_stmt_execute($st) && mysqli_stmt_affected_rows($st) > 0;
    mysqli_stmt_close($st);

    return $ok;
}