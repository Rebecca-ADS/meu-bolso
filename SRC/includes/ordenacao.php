<?php

// Título de coluna clicável: alterna entre crescente e decrescente
// $extras guarda filtros que devem continuar na URL ao reordenar
function titulo_ordenavel(string $coluna, string $rotulo, string $ordem, string $dir, array $extras = []): string
{
    $novaDir = ($ordem === $coluna && $dir === 'asc') ? 'desc' : 'asc';
    $seta = '';
    if ($ordem === $coluna) {
        $seta = ($dir === 'asc') ? ' ▲' : ' ▼';
    }

    $params = array_merge($extras, ['ordem' => $coluna, 'dir' => $novaDir]);
    $url = '?' . http_build_query($params);

    return '<a href="' . e($url) . '">' . e($rotulo) . $seta . '</a>';
}