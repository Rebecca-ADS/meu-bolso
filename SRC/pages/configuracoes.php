<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Usuario.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idUsuario = (int) $_SESSION['usuario_id'];
$usuarioObj = new Usuario($pdo);

$msgSenha = '';
$erroSenha = false;
$msgExcluir = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $acao = $_POST['acao'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token) || !is_string($acao)) {
        $msgSenha = 'Formulário inválido. Atualize a página e tente novamente.';
        $erroSenha = true;
    } elseif ($acao === 'senha') {
        $atual = $_POST['senha_atual'] ?? '';
        $nova = $_POST['nova_senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        if (!is_string($atual) || !is_string($nova) || !is_string($confirmar)) {
            $msgSenha = 'Dados inválidos.';
            $erroSenha = true;
        } elseif (strlen($nova) < 8 || strlen($nova) > 4096) {
            $msgSenha = 'A nova senha deve ter pelo menos 8 caracteres.';
            $erroSenha = true;
        } elseif ($nova !== $confirmar) {
            $msgSenha = 'As senhas não coincidem.';
            $erroSenha = true;
        } else {
            try {
                if ($usuarioObj->alterarSenha($idUsuario, $atual, $nova)) {
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $msgSenha = 'Senha alterada com sucesso!';
                } else {
                    $msgSenha = 'A senha atual está incorreta.';
                    $erroSenha = true;
                }
            } catch (Throwable $e) {
                error_log('Erro ao alterar senha: ' . $e->getMessage());
                $msgSenha = 'Não foi possível alterar agora. Tente novamente.';
                $erroSenha = true;
            }
        }
    } elseif ($acao === 'excluir') {
        $senha = $_POST['senha_excluir'] ?? '';

        if (!is_string($senha) || $senha === '') {
            $msgExcluir = 'Informe sua senha para excluir a conta.';
        } else {
            try {
                if ($usuarioObj->excluirConta($idUsuario, $senha)) {
                    $_SESSION = [];
                    if (ini_get('session.use_cookies')) {
                        $p = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
                    }
                    session_destroy();

                    header('Location: ../index.php');
                    exit;
                }

                $msgExcluir = 'Senha incorreta. A conta não foi excluída.';
            } catch (Throwable $e) {
                error_log('Erro ao excluir conta: ' . $e->getMessage());
                $msgExcluir = 'Não foi possível excluir agora. Tente novamente.';
            }
        }
    }
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Meu Bolso</title>

    <style>
        * { box-sizing: border-box; }

        body { margin: 0; background: #f4f7fb; color: #243047; font-family: Arial, sans-serif; }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 18px 8%;
            background: #173b67;
            color: #fff;
        }

        header h1 { margin: 0; font-size: 24px; }
        header a { color: #fff; }

        main { max-width: 520px; margin: 32px auto; padding: 0 16px; }

        .painel {
            padding: 28px;
            margin-bottom: 24px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px #00000010;
        }

        .painel.perigo { border: 1px solid #f1aeb5; }
        h2 { margin: 0 0 20px; }
        p.texto { line-height: 1.5; }

        .mensagem { margin-bottom: 18px; padding: 12px; border-radius: 8px; line-height: 1.4; }
        .mensagem.erro { color: #842029; background: #f8d7da; }
        .mensagem.ok { color: #0f5132; background: #d1e7dd; }

        .campo { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }

        input {
            width: 100%;
            min-height: 44px;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 16px;
        }

        input:focus { outline: 2px solid #8bbcf0; outline-offset: 1px; }

        button {
            width: 100%;
            min-height: 46px;
            border: 0;
            border-radius: 7px;
            background: #216eaf;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover { background: #185b94; }
        button.excluir { background: #b42318; }
        button.excluir:hover { background: #8f1c12; }
    </style>
</head>
<body>
    <header>
        <h1>Meu Bolso</h1>
        <div>
            <a href="dashboard.php">Dashboard</a> |
            <a href="historico.php">Histórico</a> |
            <a href="graficos.php">Gráficos</a> |
            <a href="perfil.php">Perfil</a> |
            <a href="configuracoes.php">Configurações</a> |
            <a href="logout.php">Sair</a>
        </div>
    </header>

    <main>
        <section class="painel">
            <h2>Alterar senha</h2>

            <?php if ($msgSenha !== ''): ?>
                <p class="mensagem <?= $erroSenha ? 'erro' : 'ok' ?>" role="status">
                    <?= e($msgSenha) ?>
                </p>
            <?php endif; ?>

            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="acao" value="senha">

                <div class="campo">
                    <label for="senha_atual">Senha atual</label>
                    <input type="password" id="senha_atual" name="senha_atual"
                           maxlength="4096" autocomplete="current-password" required>
                </div>

                <div class="campo">
                    <label for="nova_senha">Nova senha (mínimo de 8 caracteres)</label>
                    <input type="password" id="nova_senha" name="nova_senha" minlength="8"
                           maxlength="4096" autocomplete="new-password" required>
                </div>

                <div class="campo">
                    <label for="confirmar_senha">Confirmar nova senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="8"
                           maxlength="4096" autocomplete="new-password" required>
                </div>

                <button type="submit">Alterar senha</button>
            </form>
        </section>

        <section class="painel perigo">
            <h2>Excluir conta</h2>

            <p class="texto">
                Isso apaga sua conta e <strong>todos os seus lançamentos</strong>.
                Não dá para desfazer.
            </p>

            <?php if ($msgExcluir !== ''): ?>
                <p class="mensagem erro" role="alert"><?= e($msgExcluir) ?></p>
            <?php endif; ?>

            <form
                action=""
                method="POST"
                onsubmit="return confirm('Tem certeza? Sua conta e todos os lançamentos serão apagados.');"
            >
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="acao" value="excluir">

                <div class="campo">
                    <label for="senha_excluir">Digite sua senha para confirmar</label>
                    <input type="password" id="senha_excluir" name="senha_excluir"
                           maxlength="4096" autocomplete="current-password" required>
                </div>

                <button class="excluir" type="submit">Excluir minha conta</button>
            </form>
        </section>
    </main>
</body>
</html>