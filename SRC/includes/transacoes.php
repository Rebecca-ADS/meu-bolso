<?php

// Totais de receitas e despesas (todo o período ou entre duas datas)
function transacao_totais($conexao, int $idUsuario, ?string $inicio = null, ?string $fim = null): array
{
    $sql = "SELECT
                COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor END), 0),
                COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor END), 0)
            FROM transacoes
            WHERE id_usuario = ?";
    $porPeriodo = ($inicio !== null && $fim !== null);
    if ($porPeriodo) {
        $sql .= ' AND data_transacao BETWEEN ? AND ?';
    }

    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return ['receitas' => 0.0, 'despesas' => 0.0];
    }
    if ($porPeriodo) {
        mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fim);
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $receitas, $despesas);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return ['receitas' => (float) $receitas, 'despesas' => (float) $despesas];
}

// Últimos lançamentos do usuário (com a imagem da categoria, se o admin escolheu uma)
function transacao_listar($conexao, int $idUsuario, int $limite = 10): array
{
    $sql = 'SELECT t.id_transacao, t.tipo, t.descricao, t.valor, t.data_transacao, c.nome, i.arquivo
            FROM transacoes t
            INNER JOIN categorias c ON c.id_categoria = t.id_categoria
            LEFT JOIN imagens i ON i.id_imagem = c.id_imagem
            WHERE t.id_usuario = ?
            ORDER BY t.data_transacao DESC, t.id_transacao DESC
            LIMIT ?';
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $limite);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $tipo, $descricao, $valor, $data, $categoria, $imagem);

    $lista = [];
    while (mysqli_stmt_fetch($stmt)) {
        $lista[] = [
            'id_transacao'   => $id,
            'tipo'           => $tipo,
            'descricao'      => $descricao,
            'valor'          => (float) $valor,
            'data_transacao' => $data,
            'categoria'      => $categoria,
            'imagem'         => $imagem,
        ];
    }
    mysqli_stmt_close($stmt);
    return $lista;
}

// Histórico completo, ordenável (a coluna vem de uma lista fixa, nunca direto do usuário)
function transacao_historico($conexao, int $idUsuario, string $ordem = 'data', string $dir = 'desc', string $tipo = ''): array
{
    $colunas = [
        'data'      => 't.data_transacao',
        'descricao' => 't.descricao',
        'categoria' => 'c.nome',
        'tipo'      => 't.tipo',
        'valor'     => 't.valor',
    ];
    $colunaSql = $colunas[$ordem] ?? 't.data_transacao';
    $direcaoSql = ($dir === 'asc') ? 'ASC' : 'DESC';

    $sql = 'SELECT t.id_transacao, t.tipo, t.descricao, t.valor, t.data_transacao, c.nome, i.arquivo
            FROM transacoes t
            INNER JOIN categorias c ON c.id_categoria = t.id_categoria
            LEFT JOIN imagens i ON i.id_imagem = c.id_imagem
            WHERE t.id_usuario = ?';
    $filtrar = in_array($tipo, ['receita', 'despesa'], true);
    if ($filtrar) {
        $sql .= ' AND t.tipo = ?';
    }
    $sql .= " ORDER BY $colunaSql $direcaoSql, t.id_transacao DESC";

    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return [];
    }
    if ($filtrar) {
        mysqli_stmt_bind_param($stmt, 'is', $idUsuario, $tipo);
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $tipoLinha, $descricao, $valor, $data, $categoria, $imagem);

    $lista = [];
    while (mysqli_stmt_fetch($stmt)) {
        $lista[] = [
            'id_transacao'   => $id,
            'tipo'           => $tipoLinha,
            'descricao'      => $descricao,
            'valor'          => (float) $valor,
            'data_transacao' => $data,
            'categoria'      => $categoria,
            'imagem'         => $imagem,
        ];
    }
    mysqli_stmt_close($stmt);
    return $lista;
}

// Despesas agrupadas por categoria em um período (gráfico de pizza)
function transacao_despesas_por_categoria($conexao, int $idUsuario, string $inicio, string $fim): array
{
    $sql = "SELECT c.nome, SUM(t.valor) AS total
            FROM transacoes t
            INNER JOIN categorias c ON c.id_categoria = t.id_categoria
            WHERE t.id_usuario = ? AND t.tipo = 'despesa' AND t.data_transacao BETWEEN ? AND ?
            GROUP BY c.id_categoria, c.nome
            ORDER BY total DESC";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $inicio, $fim);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $categoria, $total);

    $lista = [];
    while (mysqli_stmt_fetch($stmt)) {
        $lista[] = ['categoria' => $categoria, 'total' => (float) $total];
    }
    mysqli_stmt_close($stmt);
    return $lista;
}

// Total por mês e tipo desde uma data (gráfico de colunas)
function transacao_resumo_mensal($conexao, int $idUsuario, string $desde): array
{
    $sql = "SELECT DATE_FORMAT(data_transacao, '%Y-%m') AS mes, tipo, SUM(valor) AS total
            FROM transacoes
            WHERE id_usuario = ? AND data_transacao >= ?
            GROUP BY DATE_FORMAT(data_transacao, '%Y-%m'), tipo
            ORDER BY mes";
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'is', $idUsuario, $desde);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $mes, $tipo, $total);

    $lista = [];
    while (mysqli_stmt_fetch($stmt)) {
        $lista[] = ['mes' => $mes, 'tipo' => $tipo, 'total' => (float) $total];
    }
    mysqli_stmt_close($stmt);
    return $lista;
}

// Exclui um lançamento (só se for do próprio usuário)
function transacao_excluir($conexao, int $idTransacao, int $idUsuario): bool
{
    $stmt = mysqli_prepare($conexao, 'DELETE FROM transacoes WHERE id_transacao = ? AND id_usuario = ?');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $idTransacao, $idUsuario);
    mysqli_stmt_execute($stmt);
    $apagou = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $apagou;
}

// Categorias (para o formulário de novo lançamento)
function categoria_listar($conexao): array
{
    $resultado = mysqli_query($conexao, 'SELECT id_categoria, nome, tipo FROM categorias ORDER BY tipo, nome');
    $lista = [];
    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $lista[] = $linha;
        }
        mysqli_free_result($resultado);
    }
    return $lista;
}

// Tipo (receita/despesa) de uma categoria, ou null se não existir
function categoria_tipo($conexao, int $idCategoria): ?string
{
    $stmt = mysqli_prepare($conexao, 'SELECT tipo FROM categorias WHERE id_categoria = ?');
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'i', $idCategoria);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $tipo);
    $achou = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $achou ? $tipo : null;
}

// CRUD - CREATE: cria um lançamento
function transacao_criar($conexao, int $idUsuario, int $idCategoria, string $tipo, string $descricao, float $valor, string $data): bool
{
    $sql = 'INSERT INTO transacoes (id_usuario, id_categoria, tipo, descricao, valor, data_transacao)
            VALUES (?, ?, ?, ?, ?, ?)';
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'iissds', $idUsuario, $idCategoria, $tipo, $descricao, $valor, $data);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Busca UM lançamento do usuário (null se não existir ou for de outro usuário)
function transacao_buscar($conexao, int $idTransacao, int $idUsuario): ?array
{
    $sql = 'SELECT t.id_transacao, t.tipo, t.descricao, t.valor, t.data_transacao, t.criado_em, c.nome
            FROM transacoes t
            INNER JOIN categorias c ON c.id_categoria = t.id_categoria
            WHERE t.id_transacao = ? AND t.id_usuario = ?';
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $idTransacao, $idUsuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $tipo, $descricao, $valor, $data, $criado, $categoria);

    $linha = null;
    if (mysqli_stmt_fetch($stmt)) {
        $linha = [
            'id_transacao'   => $id,
            'tipo'           => $tipo,
            'descricao'      => $descricao,
            'valor'          => (float) $valor,
            'data_transacao' => $data,
            'criado_em'      => $criado,
            'categoria'      => $categoria,
        ];
    }
    mysqli_stmt_close($stmt);
    return $linha;
}

// Imagens (comprovantes) ligadas a um lançamento do usuário
function transacao_imagens($conexao, int $idTransacao, int $idUsuario): array
{
    $sql = 'SELECT id_imagem, titulo, arquivo
            FROM imagens
            WHERE id_transacao = ? AND id_usuario = ?
            ORDER BY criado_em DESC';
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return [];
    }
    mysqli_stmt_bind_param($stmt, 'ii', $idTransacao, $idUsuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $titulo, $arquivo);

    $lista = [];
    while (mysqli_stmt_fetch($stmt)) {
        $lista[] = ['id_imagem' => $id, 'titulo' => $titulo, 'arquivo' => $arquivo];
    }
    mysqli_stmt_close($stmt);
    return $lista;
}

// CRUD - READ: dados de um lançamento do usuário para preencher o formulário de edição
function transacao_para_edicao($conexao, int $idTransacao, int $idUsuario): ?array
{
    $sql = 'SELECT id_categoria, tipo, descricao, valor, data_transacao
            FROM transacoes
            WHERE id_transacao = ? AND id_usuario = ?';
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $idTransacao, $idUsuario);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $idCategoria, $tipo, $descricao, $valor, $data);

    $linha = null;
    if (mysqli_stmt_fetch($stmt)) {
        $linha = [
            'id_categoria'   => $idCategoria,
            'tipo'           => $tipo,
            'descricao'      => $descricao,
            'valor'          => (float) $valor,
            'data_transacao' => $data,
        ];
    }
    mysqli_stmt_close($stmt);
    return $linha;
}

// CRUD - UPDATE: atualiza um lançamento (só se for do próprio usuário)
function transacao_atualizar($conexao, int $idTransacao, int $idUsuario, int $idCategoria, string $tipo, string $descricao, float $valor, string $data): bool
{
    $sql = 'UPDATE transacoes
            SET id_categoria = ?, tipo = ?, descricao = ?, valor = ?, data_transacao = ?
            WHERE id_transacao = ? AND id_usuario = ?';
    $stmt = mysqli_prepare($conexao, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'issdsii', $idCategoria, $tipo, $descricao, $valor, $data, $idTransacao, $idUsuario);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

