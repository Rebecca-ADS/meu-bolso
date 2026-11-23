<?php

// Soma de receitas e despesas do usuário (opcionalmente dentro de um período)
function transacao_totais($conexao, int $idUsuario, ?string $inicio = null, ?string $fim = null): array
{
    $totais = ['receitas' => 0.0, 'despesas' => 0.0];

    if ($inicio !== null && $fim !== null) {
        $st = mysqli_prepare(
            $conexao,
            'SELECT tipo, SUM(valor) FROM transacoes
             WHERE id_usuario = ? AND data_transacao BETWEEN ? AND ?
             GROUP BY tipo'
        );
        mysqli_stmt_bind_param($st, 'iss', $idUsuario, $inicio, $fim);
    } else {
        $st = mysqli_prepare(
            $conexao,
            'SELECT tipo, SUM(valor) FROM transacoes WHERE id_usuario = ? GROUP BY tipo'
        );
        mysqli_stmt_bind_param($st, 'i', $idUsuario);
    }

    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $tipo, $soma);

    while (mysqli_stmt_fetch($st)) {
        if ($tipo === 'receita') {
            $totais['receitas'] = (float) $soma;
        } elseif ($tipo === 'despesa') {
            $totais['despesas'] = (float) $soma;
        }
    }
    mysqli_stmt_close($st);

    return $totais;
}

// Últimos lançamentos do usuário, com o nome da categoria
function transacao_listar($conexao, int $idUsuario, int $limite = 10): array
{
    $st = mysqli_prepare(
        $conexao,
        'SELECT t.id_transacao, t.tipo, t.descricao, t.valor, t.data_transacao, c.nome
         FROM transacoes t
         INNER JOIN categorias c ON c.id_categoria = t.id_categoria
         WHERE t.id_usuario = ?
         ORDER BY t.data_transacao DESC, t.id_transacao DESC
         LIMIT ?'
    );
    mysqli_stmt_bind_param($st, 'ii', $idUsuario, $limite);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $id, $tipo, $descricao, $valor, $data, $categoria);

    $lista = [];
    while (mysqli_stmt_fetch($st)) {
        $lista[] = [
            'id_transacao'   => $id,
            'tipo'           => $tipo,
            'descricao'      => $descricao,
            'valor'          => $valor,
            'data_transacao' => $data,
            'categoria'      => $categoria,
        ];
    }
    mysqli_stmt_close($st);

    return $lista;
}

// Histórico completo, com ordenação por coluna e filtro opcional por tipo
function transacao_historico($conexao, int $idUsuario, string $ordem = 'data', string $dir = 'desc', string $tipo = ''): array
{
    // Lista fixa: só estes nomes podem ir para o ORDER BY (evita SQL Injection)
    $colunas = [
        'data'      => 't.data_transacao',
        'descricao' => 't.descricao',
        'categoria' => 'c.nome',
        'tipo'      => 't.tipo',
        'valor'     => 't.valor',
    ];
    $campo = $colunas[$ordem] ?? $colunas['data'];
    $sentido = ($dir === 'asc') ? 'ASC' : 'DESC';

    $sql = 'SELECT t.id_transacao, t.tipo, t.descricao, t.valor, t.data_transacao, c.nome
            FROM transacoes t
            INNER JOIN categorias c ON c.id_categoria = t.id_categoria
            WHERE t.id_usuario = ?';

    $filtraTipo = ($tipo === 'receita' || $tipo === 'despesa');
    if ($filtraTipo) {
        $sql .= ' AND t.tipo = ?';
    }
    $sql .= ' ORDER BY ' . $campo . ' ' . $sentido . ', t.id_transacao DESC';

    $st = mysqli_prepare($conexao, $sql);
    if ($filtraTipo) {
        mysqli_stmt_bind_param($st, 'is', $idUsuario, $tipo);
    } else {
        mysqli_stmt_bind_param($st, 'i', $idUsuario);
    }
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $id, $tipoLinha, $descricao, $valor, $data, $categoria);

    $lista = [];
    while (mysqli_stmt_fetch($st)) {
        $lista[] = [
            'id_transacao'   => $id,
            'tipo'           => $tipoLinha,
            'descricao'      => $descricao,
            'valor'          => $valor,
            'data_transacao' => $data,
            'categoria'      => $categoria,
        ];
    }
    mysqli_stmt_close($st);

    return $lista;
}

// Despesas agrupadas por categoria dentro de um período (para o gráfico de rosca)
function transacao_despesas_por_categoria($conexao, int $idUsuario, string $inicio, string $fim): array
{
    $st = mysqli_prepare(
        $conexao,
        "SELECT c.nome, SUM(t.valor) AS total
         FROM transacoes t
         INNER JOIN categorias c ON c.id_categoria = t.id_categoria
         WHERE t.id_usuario = ? AND t.tipo = 'despesa' AND t.data_transacao BETWEEN ? AND ?
         GROUP BY c.id_categoria, c.nome
         ORDER BY total DESC"
    );
    mysqli_stmt_bind_param($st, 'iss', $idUsuario, $inicio, $fim);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $categoria, $total);

    $lista = [];
    while (mysqli_stmt_fetch($st)) {
        $lista[] = ['categoria' => $categoria, 'total' => (float) $total];
    }
    mysqli_stmt_close($st);

    return $lista;
}

// Receitas e despesas somadas por mês, a partir de uma data (gráfico de colunas)
function transacao_resumo_mensal($conexao, int $idUsuario, string $desde): array
{
    $st = mysqli_prepare(
        $conexao,
        "SELECT DATE_FORMAT(data_transacao, '%Y-%m') AS mes, tipo, SUM(valor) AS total
         FROM transacoes
         WHERE id_usuario = ? AND data_transacao >= ?
         GROUP BY mes, tipo
         ORDER BY mes"
    );
    mysqli_stmt_bind_param($st, 'is', $idUsuario, $desde);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $mes, $tipo, $total);

    $lista = [];
    while (mysqli_stmt_fetch($st)) {
        $lista[] = ['mes' => $mes, 'tipo' => $tipo, 'total' => (float) $total];
    }
    mysqli_stmt_close($st);

    return $lista;
}

// Exclui só se o lançamento for do usuário logado
function transacao_excluir($conexao, int $idTransacao, int $idUsuario): bool
{
    $st = mysqli_prepare(
        $conexao,
        'DELETE FROM transacoes WHERE id_transacao = ? AND id_usuario = ?'
    );
    mysqli_stmt_bind_param($st, 'ii', $idTransacao, $idUsuario);
    mysqli_stmt_execute($st);
    $apagou = mysqli_stmt_affected_rows($st) > 0;
    mysqli_stmt_close($st);

    return $apagou;
}

// Todas as categorias (receita e despesa)
function categoria_listar($conexao): array
{
    $res = mysqli_query($conexao, 'SELECT id_categoria, nome, tipo FROM categorias ORDER BY tipo, nome');
    $lista = [];
    while ($linha = mysqli_fetch_assoc($res)) {
        $lista[] = $linha;
    }
    return $lista;
}

// Tipo (receita/despesa) de uma categoria, ou null se ela não existir
function categoria_tipo($conexao, int $idCategoria): ?string
{
    $st = mysqli_prepare($conexao, 'SELECT tipo FROM categorias WHERE id_categoria = ? LIMIT 1');
    mysqli_stmt_bind_param($st, 'i', $idCategoria);
    mysqli_stmt_execute($st);
    mysqli_stmt_bind_result($st, $tipo);
    $achou = mysqli_stmt_fetch($st);
    mysqli_stmt_close($st);

    return $achou ? $tipo : null;
}

// Cria um lançamento para o usuário
function transacao_criar($conexao, int $idUsuario, int $idCategoria, string $tipo, string $descricao, float $valor, string $data): bool
{
    $st = mysqli_prepare(
        $conexao,
        'INSERT INTO transacoes (id_usuario, id_categoria, tipo, descricao, valor, data_transacao)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    mysqli_stmt_bind_param($st, 'iissds', $idUsuario, $idCategoria, $tipo, $descricao, $valor, $data);
    $ok = mysqli_stmt_execute($st);
    mysqli_stmt_close($st);

    return $ok;
}