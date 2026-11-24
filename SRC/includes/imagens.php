<?php

// CRUD de imagens (biblioteca do sistema, gerenciada pelo administrador)
// Create: imagem_salvar | Read: imagem_listar e imagem_buscar
// Update: imagem_atualizar | Delete: imagem_excluir

// CRUD - READ: lista a biblioteca de imagens (ordenável) com a quantidade de categorias que usam cada uma
function imagem_listar($conexao, string $ordem = 'criado', string $dir = 'desc'): array
{
    // A coluna de ordenação vem de uma lista fixa, nunca direto do usuário
    $colunas = ['titulo' => 'i.titulo', 'criado' => 'i.criado_em', 'uso' => 'uso'];
    $colunaSql = $colunas[$ordem] ?? 'i.criado_em';
    $direcaoSql = ($dir === 'asc') ? 'ASC' : 'DESC';

    $sql = "SELECT i.id_imagem, i.titulo, i.arquivo, i.criado_em, COUNT(c.id_categoria) AS uso
            FROM imagens i
            LEFT JOIN categorias c ON c.id_imagem = i.id_imagem
            WHERE i.id_transacao IS NULL
            GROUP BY i.id_imagem, i.titulo, i.arquivo, i.criado_em
            ORDER BY $colunaSql $direcaoSql, i.id_imagem DESC";
    $resultado = mysqli_query($conexao, $sql);

    $lista = [];
    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $lista[] = $linha;
        }
        mysqli_free_result($resultado);
    }
    return $lista;
}

// CRUD - READ: busca uma imagem (null se não existir)
function imagem_buscar($conexao, int $idImagem): ?array
{
    $stmt = mysqli_prepare($conexao, 'SELECT id_imagem, titulo, arquivo FROM imagens WHERE id_imagem = ? AND id_transacao IS NULL');
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'i', $idImagem);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $titulo, $arquivo);

    $linha = null;
    if (mysqli_stmt_fetch($stmt)) {
        $linha = ['id_imagem' => $id, 'titulo' => $titulo, 'arquivo' => $arquivo];
    }
    mysqli_stmt_close($stmt);
    return $linha;
}

// CRUD - CREATE: grava os dados de uma imagem já enviada
function imagem_salvar($conexao, int $idUsuario, string $titulo, string $arquivo): bool
{
    $stmt = mysqli_prepare($conexao, 'INSERT INTO imagens (id_usuario, titulo, arquivo) VALUES (?, ?, ?)');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'iss', $idUsuario, $titulo, $arquivo);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// CRUD - UPDATE: atualiza título e arquivo
function imagem_atualizar($conexao, int $idImagem, string $titulo, string $arquivo): bool
{
    $stmt = mysqli_prepare($conexao, 'UPDATE imagens SET titulo = ?, arquivo = ? WHERE id_imagem = ?');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'ssi', $titulo, $arquivo, $idImagem);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// CRUD - DELETE: remove o registro (as categorias que usavam a imagem ficam sem imagem)
function imagem_excluir($conexao, int $idImagem): bool
{
    $stmt = mysqli_prepare($conexao, 'DELETE FROM imagens WHERE id_imagem = ?');
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'i', $idImagem);
    mysqli_stmt_execute($stmt);
    $apagou = mysqli_stmt_affected_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $apagou;
}