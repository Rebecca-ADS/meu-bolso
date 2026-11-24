<?php

require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/auth.php';

iniciar_sessao();

$logado = usuario_logado();

$raiz = '';
$titulo = 'Início';
require __DIR__ . '/includes/cabecalho.php';
?>
<section class="cartao estreito" style="text-align: center;">
    <img class="mascote-img" src="assets/img/cofrinho.svg" alt="Cofrinho do Meu Bolso">
    <h1>Meu Bolso</h1>
    <p>Controle suas receitas e despesas de forma simples e segura.</p>

    <?php if ($logado): ?>
        <p>Olá, <strong><?= e($_SESSION['usuario_nome']) ?></strong>!</p>
        <a class="botao" href="pages/<?= e(pagina_inicial(nivel_atual())) ?>">Ir para o meu painel</a>
    <?php else: ?>
        <a class="botao" href="pages/login.php">Entrar</a>
        <a class="botao neutro" href="pages/cadastro.php">Criar conta</a>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/rodape.php'; ?>