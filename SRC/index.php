<?php

require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/auth.php';

iniciar_sessao();

$raiz = '';
$titulo = 'Início';
require __DIR__ . '/includes/cabecalho.php';
?>
<section class="cartao">
    <h1>Bem-vinda ao Meu Bolso</h1>
    <p>Sistema de controle financeiro pessoal: registre receitas e despesas, acompanhe o histórico e veja seus gastos em gráficos.</p>

    <?php if (usuario_logado()): ?>
        <p>Olá, <strong><?= e($_SESSION['usuario_nome']) ?></strong>!</p>
        <a class="botao" href="pages/dashboard.php">Ir para o Dashboard</a>
    <?php else: ?>
        <a class="botao" href="pages/login.php">Entrar</a>
        <a class="botao neutro" href="pages/cadastro.php">Criar conta</a>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/rodape.php'; ?>