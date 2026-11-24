<?php

// Lista de categorias com a quantidade de lançamentos e a imagem escolhida pelo admin
function categoria_listar_com_uso($conexao, string $ordem = 'nome', string $dir = 'asc'): array
{
    // Lista fixa: só estes nomes podem ir para o ORDER BY
    $colunas = [
        'id'   => 'c.id_categoria',
        'nome' => 'c.nome',
        'tipo' => 'c.tipo',
        'uso'  => 'total',
    ];
    $campo = $colunas[$ordem] ?? $colunas['nome'];
    $sentido = ($dir === 'desc') ? 'DESC' : 'ASC';

    $res = mysqli_query(
        $conexao,
        'SELECT c.id_categoria, c.nome, c.tipo, c.id_imagem, i.arquivo AS imagem, COUNT(t.id_transacao) AS total
         FROM categorias c
         LEFT JOIN imagens i ON i.id_imagem = c.id_imagem
         LEFT JOIN transacoes t ON t.id_categoria = c.id_categoria
         GROUP BY c.id_categoria, c.nome, c.tipo, c.id_imagem, i.arquivo
         ORDER BY ' . $campo . ' ' . $sentido . ', c.id_categoria ASC'
    );

    $lista = [];
    while ($linha = mysqli_fetch_assoc($res)) {
        $lista[] = $linha;
    }
    return $lista;
}

function categoria_existe($conexao, string $nome, string $tipo): bool
{
    $st = mysqli_prepare($conexao, 'SELECT 1 FROM categorias WHERE nome = ? AND tipo = ? LIMIT 1');
    mysqli_stmt_bind_param($st, 'ss', $nome, $tipo);
    mysqli_stmt_execute($st);
    mysqli_stmt_store_result($st);
    $existe = mysqli_stmt_num_rows($st) > 0;
    mysqli_stmt_close($st);

    return $existe;
}

// Cadastra a categoria (a imagem da biblioteca é opcional)
function categoria_cadastrar($conexao, string $nome, string $tipo, ?int $idImagem = null): bool
{
    $st = mysqli_prepare($conexao, 'INSERT INTO categorias (nome, tipo, id_imagem) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($st, 'ssi', $nome, $tipo, $idImagem);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}

// Define (ou retira, com null) a imagem de uma categoria
function categoria_definir_imagem($conexao, int $idCategoria, ?int $idImagem): bool
{
    $st = mysqli_prepare($conexao, 'UPDATE categorias SET id_imagem = ? WHERE id_categoria = ?');
    mysqli_stmt_bind_param($st, 'ii', $idImagem, $idCategoria);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}

// Verdadeiro se existe algum lançamento usando a categoria
function categoria_em_uso($conexao, int $idCategoria): bool
{
    $st = mysqli_prepare($conexao, 'SELECT 1 FROM transacoes WHERE id_categoria = ? LIMIT 1');
    mysqli_stmt_bind_param($st, 'i', $idCategoria);
    mysqli_stmt_execute($st);
    mysqli_stmt_store_result($st);
    $usada = mysqli_stmt_num_rows($st) > 0;
    mysqli_stmt_close($st);

    return $usada;
}

function categoria_excluir($conexao, int $idCategoria): bool
{
    $st = mysqli_prepare($conexao, 'DELETE FROM categorias WHERE id_categoria = ?');
    mysqli_stmt_bind_param($st, 'i', $idCategoria);
    $ok = mysqli_stmt_execute($st) && mysqli_stmt_affected_rows($st) > 0;
    mysqli_stmt_close($st);

    return $ok;
}