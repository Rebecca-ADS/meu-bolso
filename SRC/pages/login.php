<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/usuarios.php';
require_once dirname(__DIR__) . '/conecta.php';

iniciar_sessao();

if (usuario_logado()) {
    header('Location: ' . pagina_inicial(nivel_atual()));
    exit;
}

$mensagem = '';
$email = '';
$aviso = $_SESSION['flash_login'] ?? '';
unset($_SESSION['flash_login']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailPost = $_POST['email'] ?? '';
    $senhaPost = $_POST['senha'] ?? '';

    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
    } elseif (!is_string($emailPost) || !is_string($senhaPost) || $senhaPost === '' || strlen($senhaPost) > 4096) {
        $mensagem = 'E-mail ou senha incorretos.';
    } else {
        $email = mb_strtolower(trim($emailPost), 'UTF-8');
        $u = usuario_autenticar($conexao, $email, $senhaPost);

        if ($u !== null) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $u['id_usuario'];
            $_SESSION['usuario_nome'] = $u['nome'];
            $_SESSION['nivel'] = (int) $u['nivel'];
            // Cada nível vai para a sua própria página inicial
            header('Location: ' . pagina_inicial((int) $u['nivel']));
            exit;
        }
        $mensagem = 'E-mail ou senha incorretos.';
    }
}

$raiz = '../';
$titulo = 'Entrar';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<section class="cartao estreito">
    <h1>Entrar</h1>

    <?php if ($aviso !== ''): ?><p class="aviso ok"><?= e($aviso) ?></p><?php endif; ?>
    <?php if ($mensagem !== ''): ?><p class="aviso erro" role="alert"><?= e($mensagem) ?></p><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= e($email) ?>" required>
        </div>
        <div class="campo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" maxlength="4096" required>
        </div>
        <button class="cheio" type="submit">Entrar</button>
    </form>
</section>

<img class="mascote-img" src="../assets/img/cofrinho.svg" alt="Cofrinho de porquinho">
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>