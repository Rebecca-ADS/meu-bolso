<?php
// Cabeçalho comum a todas as páginas.
// Variáveis esperadas: $titulo e $raiz ('' em SRC/ e '../' em SRC/pages/)
$pg = ($raiz === '') ? 'pages/' : '';
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
            <?php if (nivel_atual() === NIVEL_USUARIO): ?>
                <a href="<?= e($pg) ?>dashboard.php">Dashboard</a>
                <a href="<?= e($pg) ?>historico.php">Histórico</a>
                <a href="<?= e($pg) ?>graficos.php">Gráficos</a>
            <?php endif; ?>
            <?php if (nivel_atual() === NIVEL_GERENTE): ?>
                <a href="<?= e($pg) ?>relatorio_geral.php">Relatório de cadastros</a>
            <?php endif; ?>
            <?php if (nivel_atual() === NIVEL_ADMIN): ?>
                <a href="<?= e($pg) ?>usuarios.php">Usuários</a>
                <a href="<?= e($pg) ?>categorias.php">Categorias</a>
                <a href="<?= e($pg) ?>imagens.php">Imagens</a>
            <?php endif; ?>
            <a href="<?= e($pg) ?>perfil.php">Perfil</a>
            <a class="sair" href="<?= e($pg) ?>logout.php">Sair (<?= e(nome_nivel(nivel_atual())) ?>)</a>
        <?php else: ?>
            <a href="<?= e($pg) ?>login.php">Entrar</a>
            <a href="<?= e($pg) ?>cadastro.php">Criar conta</a>
        <?php endif; ?>
    </nav>
</header>
<main class="conteudo"></main>