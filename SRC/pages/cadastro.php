<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/usuarios.php';
require_once dirname(__DIR__) . '/conecta.php';

iniciar_sessao();

$mensagem = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomePost = $_POST['nome'] ?? '';
    $emailPost = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
    } elseif (!is_string($nomePost) || !is_string($emailPost) || !is_string($senha) || !is_string($confirmar)) {
        $mensagem = 'Dados inválidos.';
    } else {
        $nome = trim($nomePost);
        $email = mb_strtolower(trim($emailPost), 'UTF-8');

        if (
            $nome === '' || mb_strlen($nome, 'UTF-8') > 100 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email, 'UTF-8') > 150 ||
            strlen($senha) < 8 || strlen($senha) > 4096
        ) {
            $mensagem = 'Confira o nome, o e-mail e a senha (mínimo de 8 caracteres).';
        } elseif ($senha !== $confirmar) {
            $mensagem = 'As senhas não coincidem.';
        } elseif (usuario_cadastrar($conexao, $nome, $email, $senha, NIVEL_USUARIO)) {
            $_SESSION['flash_login'] = 'Conta criada com sucesso! Entre com seu e-mail e senha.';
            header('Location: login.php');
            exit;
        } else {
            $mensagem = 'Este e-mail já está cadastrado.';
        }
    }
}

$raiz = '../';
$titulo = 'Criar conta';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<section class="cartao estreito">
    <h1>Criar minha conta</h1>

    <?php if ($mensagem !== ''): ?><p class="aviso erro" role="alert"><?= e($mensagem) ?></p><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="campo">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" maxlength="100" value="<?= e($nome) ?>" required>
        </div>
        <div class="campo">
            <label for="email">E-mail (será seu login)</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= e($email) ?>" required>
        </div>
        <div class="campo">
            <label for="senha">Senha (mínimo de 8 caracteres)</label>
            <input type="password" id="senha" name="senha" minlength="8" maxlength="4096" required>
        </div>
        <div class="campo">
            <label for="confirmar_senha">Confirmar senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8" maxlength="4096" required>
        </div>
        <button class="cheio" type="submit">Criar conta</button>
    </form>
</section>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>