<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/usuarios.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_login();

$idUsuario = (int) $_SESSION['usuario_id'];
$usuario = usuario_buscar($conexao, $idUsuario);

if ($usuario === null) {
    header('Location: logout.php');
    exit;
}

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$erroNome = '';
$erroSenha = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $erroNome = 'Formulário inválido. Atualize a página e tente novamente.';

    } elseif ($acao === 'nome') {
        $nome = $_POST['nome'] ?? '';
        $nome = is_string($nome) ? trim($nome) : '';

        if ($nome === '' || mb_strlen($nome, 'UTF-8') > 100) {
            $erroNome = 'Informe um nome com até 100 caracteres.';
        } elseif (usuario_atualizar_nome($conexao, $idUsuario, $nome)) {
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['flash'] = 'Nome atualizado com sucesso.';
            header('Location: perfil.php');
            exit;
        } else {
            $erroNome = 'Não foi possível atualizar o nome agora.';
        }

    } elseif ($acao === 'senha') {
        $atual = $_POST['senha_atual'] ?? '';
        $nova = $_POST['senha_nova'] ?? '';
        $confirmar = $_POST['senha_confirmar'] ?? '';

        if (!is_string($atual) || !is_string($nova) || !is_string($confirmar)) {
            $erroSenha = 'Dados inválidos.';
        } elseif (strlen($nova) < 8 || strlen($nova) > 4096) {
            $erroSenha = 'A nova senha deve ter no mínimo 8 caracteres.';
        } elseif ($nova !== $confirmar) {
            $erroSenha = 'A confirmação não é igual à nova senha.';
        } elseif (usuario_autenticar($conexao, $usuario['email'], $atual) === null) {
            $erroSenha = 'A senha atual está incorreta.';
        } elseif (usuario_trocar_senha($conexao, $idUsuario, $usuario['email'], $nova)) {
            session_regenerate_id(true);
            $_SESSION['flash'] = 'Senha alterada com sucesso.';
            header('Location: perfil.php');
            exit;
        } else {
            $erroSenha = 'Não foi possível alterar a senha agora.';
        }
    }
}

$raiz = '../';
$titulo = 'Perfil';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Meu perfil</h1>

<?php if ($flash !== ''): ?><p class="aviso ok" role="status"><?= e($flash) ?></p><?php endif; ?>

<section class="cartao">
    <h2>Meus dados</h2>

    <?php if ($erroNome !== ''): ?><p class="aviso erro" role="alert"><?= e($erroNome) ?></p><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="acao" value="nome">

        <div class="campo">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" maxlength="100" value="<?= e($usuario['nome']) ?>" required>
        </div>
        <div class="campo">
            <label for="email">E-mail (seu login)</label>
            <input type="email" id="email" value="<?= e($usuario['email']) ?>" disabled>
        </div>
        <div class="campo">
            <label for="nivel">Nível de acesso</label>
            <input type="text" id="nivel" value="<?= e($usuario['nivel'] . ' - ' . nome_nivel((int) $usuario['nivel'])) ?>" disabled>
        </div>
        <button type="submit">Salvar nome</button>
    </form>
</section>

<section class="cartao">
    <h2>Alterar senha</h2>

    <?php if ($erroSenha !== ''): ?><p class="aviso erro" role="alert"><?= e($erroSenha) ?></p><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="acao" value="senha">

        <div class="campo">
            <label for="senha_atual">Senha atual</label>
            <input type="password" id="senha_atual" name="senha_atual" maxlength="4096" required>
        </div>
        <div class="campo">
            <label for="senha_nova">Nova senha (mínimo de 8 caracteres)</label>
            <input type="password" id="senha_nova" name="senha_nova" minlength="8" maxlength="4096" required>
        </div>
        <div class="campo">
            <label for="senha_confirmar">Confirmar nova senha</label>
            <input type="password" id="senha_confirmar" name="senha_confirmar" minlength="8" maxlength="4096" required>
        </div>
        <button type="submit">Alterar senha</button>
    </form>
</section>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>