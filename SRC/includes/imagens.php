<?php

const IMAGEM_MAX_BYTES = 2097152; // 2 MB

function imagem_listar($conexao, int $idUsuario): array
{
    $st = mysqli_prepare(
        $conexao,
        'SELECT i.id_imagem, i.titulo, i.arquivo, i.criado_em, t.descricao
         FROM imagens i
         LEFT JOIN transacoes t ON t.id_transacao = i.id_transacao
         WHERE i.id_usuario = ?
         ORDER BY i.id_imagem DESC'
    );
    mysqli_stmt_bind_param($st, 'i', $idUsuario);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $id, $titulo, $arquivo, $criado, $descTransacao);

    $lista = [];
    while (mysqli_stmt_fetch($st)) {
        $lista[] = [
            'id_imagem' => $id,
            'titulo'    => $titulo,
            'arquivo'   => $arquivo,
            'criado_em' => $criado,
            'transacao' => $descTransacao,
        ];
    }
    mysqli_stmt_close($st);

    return $lista;
}

function imagem_salvar($conexao, int $idUsuario, ?int $idTransacao, string $titulo, string $arquivo): bool
{
    $st = mysqli_prepare(
        $conexao,
        'INSERT INTO imagens (id_usuario, id_transacao, titulo, arquivo) VALUES (?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($st, 'iiss', $idUsuario, $idTransacao, $titulo, $arquivo);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}

// Nome do arquivo de uma imagem do usuário (null se não for dele)
function imagem_arquivo($conexao, int $idImagem, int $idUsuario): ?string
{
    $st = mysqli_prepare(
        $conexao,
        'SELECT arquivo FROM imagens WHERE id_imagem = ? AND id_usuario = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($st, 'ii', $idImagem, $idUsuario);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $arquivo);
    $achou = mysqli_stmt_fetch($st);
    mysqli_stmt_close($st);

    return $achou ? $arquivo : null;
}

function imagem_excluir($conexao, int $idImagem, int $idUsuario): bool
{
    $st = mysqli_prepare($conexao, 'DELETE FROM imagens WHERE id_imagem = ? AND id_usuario = ?');
    mysqli_stmt_bind_param($st, 'ii', $idImagem, $idUsuario);
    mysqli_stmt_execute($st);
    $apagou = mysqli_stmt_affected_rows($st) > 0;
    mysqli_stmt_close($st);

    return $apagou;
}

// Confere se o lançamento pertence ao usuário
function transacao_pertence($conexao, int $idTransacao, int $idUsuario): bool
{
    $st = mysqli_prepare(
        $conexao,
        'SELECT 1 FROM transacoes WHERE id_transacao = ? AND id_usuario = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($st, 'ii', $idTransacao, $idUsuario);
    mysqli_stmt_execute($st);
    mysqli_stmt_store_result($st);
    $existe = mysqli_stmt_num_rows($st) > 0;
    mysqli_stmt_close($st);

    return $existe;
}