<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/SenhaReset.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$reset = new SenhaReset($pdo);
$mensagem = '';

// O token vem na URL (GET) e é reenviado no formulário (POST).
$tokenUrl = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['token'] ?? '')
    : ($_GET['token'] ?? '');

$tokenOk = is_string($tokenUrl)
    && preg_match('/^[a-f0-9]{64}$/', $tokenUrl) === 1
    && $reset->tokenValido($tokenUrl);

if ($tokenOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    if (!is_string($csrf) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
    } elseif (!is_string($senha) || !is_string($confirmar)) {
        $mensagem = 'Dados inválidos.';
    } elseif (strlen($senha) < 8 || strlen($senha) > 4096) {
        $mensagem = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmar) {
        $mensagem = 'As senhas não coincidem.';
    } else {
        try {
            if ($reset->redefinir($tokenUrl, $senha)) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['flash_login'] = 'Senha alterada com sucesso! Entre com a nova senha.';

                header('Location: login.php');
                exit;
            }

            $tokenOk = false; // expirou ou já foi usado
        } catch (Throwable $e) {
            error_log('Erro ao redefinir senha: ' . $e->getMessage());
            $mensagem = 'Não foi possível alterar a senha agora. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha | Meu Bolso</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f6fb;
            color: #17365d;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            width: 100%;
            max-width: 440px;
            padding: 32px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
        }

        h1 { margin: 0 0 20px; text-align: center; font-size: 28px; }
        .descricao { margin-bottom: 22px; line-height: 1.5; }

        .mensagem {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 8px;
            line-height: 1.4;
            color: #842029;
            background: #f8d7da;
        }

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
        .voltar { display: block; margin-top: 22px; text-align: center; color: #0866c6; }
    </style>
</head>
<body>
    <main class="container">
        <?php if (!$tokenOk): ?>
            <h1>Link inválido</h1>
            <p class="descricao">
                Este link de redefinição expirou, já foi usado ou não existe.
                Peça um novo link.
            </p>
            <a class="voltar" href="esqueci_senha.php">Pedir novo link</a>
        <?php else: ?>
            <h1>Nova senha</h1>

            <p class="descricao">Escolha uma nova senha com pelo menos 8 caracteres.</p>

            <?php if ($mensagem !== ''): ?>
                <p class="mensagem" role="alert">
                    <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form action="" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                >
                <input
                    type="hidden"
                    name="token"
                    value="<?= htmlspecialchars((string) $tokenUrl, ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="campo">
                    <label for="senha">Nova senha</label>
                    <input type="password" id="senha" name="senha" minlength="8"
                           maxlength="4096" autocomplete="new-password" required>
                </div>

                <div class="campo">
                    <label for="confirmar_senha">Confirmar nova senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha"
                           minlength="8" maxlength="4096" autocomplete="new-password" required>
                </div>

                <button type="submit">Salvar nova senha</button>
            </form>

            <a class="voltar" href="login.php">Voltar para o login</a>
        <?php endif; ?>
    </main>
</body>
</html>
