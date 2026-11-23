<?php

require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/auth.php';

iniciar_sessao();

// $raiz = '' quando a página está em SRC/ e '../' quando está em SRC/pages/
$raiz   = $raiz ?? '';
$titulo = $titulo ?? 'Meu Bolso';
$pg     = ($raiz === '') ? 'pages/' : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> - Meu Bolso</title>
    <link rel="stylesheet" href="<?= e($raiz) ?>assets/css/estilo.css">
</head>
<body>
<header class="topo">
    <a class="marca" href="<?= e($raiz) ?>index.php">💰 Meu Bolso</a>
    <nav>
        <?php if (usuario_logado()): ?>
            <a href="<?= e($pg) ?>dashboard.php">Dashboard</a>
            <a href="<?= e($pg) ?>historico.php">Histórico</a>
            <a href="<?= e($pg) ?>graficos.php">Gráficos</a>
            <a href="<?= e($pg) ?>imagens.php">Imagens</a>
            <?php if (nivel_atual() >= NIVEL_GERENTE): ?>
                <a href="<?= e($pg) ?>relatorio_geral.php">Relatório geral</a>
            <?php endif; ?>
            <?php if (nivel_atual() >= NIVEL_ADMIN): ?>
                <a href="<?= e($pg) ?>usuarios.php">Usuários</a>
            <?php endif; ?>
            <a href="<?= e($pg) ?>perfil.php">Perfil</a>
            <a class="sair" href="<?= e($pg) ?>logout.php">Sair (<?= e(nome_nivel(nivel_atual())) ?>)</a>
        <?php else: ?>
            <a href="<?= e($pg) ?>login.php">Entrar</a>
            <a href="<?= e($pg) ?>cadastro.php">Criar conta</a>
        <?php endif; ?>
    </nav>
</header>
<main class="conteudo">