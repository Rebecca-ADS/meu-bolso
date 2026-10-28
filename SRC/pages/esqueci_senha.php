<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/SenhaReset.php';

// ATENÇÃO: true só em desenvolvimento (XAMPP sem e-mail).
// Em produção, coloque false e envie o link por e-mail.
$modoDev = true;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensagem = '';
$erro = false;
$linkDev = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $emailPost = $_POST['email'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
        $erro = true;
    } elseif (!is_string($emailPost)) {
        $mensagem = 'Dados inválidos.';
        $erro = true;
    } else {
        $email = mb_strtolower(trim($emailPost), 'UTF-8');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email, 'UTF-8') > 150) {
            $mensagem = 'Informe um e-mail válido.';
            $erro = true;
        } else {
            try {
                $reset = new SenhaReset($pdo);
                $tokenReset = $reset->criar($email);

                // Mesma resposta exista ou não o e-mail (não revela cadastros).
                $mensagem = 'Se o e-mail estiver cadastrado, enviaremos as instruções para redefinir a senha.';

                if ($tokenReset !== null && $modoDev) {
                    $linkDev = 'redefinir_senha.php?token=' . $tokenReset;
                }
            } catch (Throwable $e) {
                error_log('Erro ao criar redefinição de senha: ' . $e->getMessage());
                $mensagem = 'Não foi possível processar agora. Tente novamente.';
                $erro = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a senha | Meu Bolso</title>

    <style>
        * {
            box-sizing: border-box;
        }

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

        h1 {
            margin: 0 0 20px;
            text-align: center;
            font-size: 28px;
        }

        .descricao {
            margin-bottom: 22px;
            line-height: 1.5;
        }

        .mensagem {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 8px;
            line-height: 1.4;
        }

        .mensagem.erro {
            color: #842029;
            background: #f8d7da;
        }

        .mensagem.ok {
            color: #0f5132;
            background: #d1e7dd;
        }

        .dev {
            margin-bottom: 18px;
            padding: 12px;
            border-radius: 8px;
            background: #fff3cd;
            color: #664d03;
            word-break: break-all;
            line-height: 1.4;
        }

        .campo {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            min-height: 44px;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 16px;
        }

        input:focus {
            outline: 2px solid #8bbcf0;
            outline-offset: 1px;
        }

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

        button:hover {
            background: #185b94;
        }

        .voltar {
            display: block;
            margin-top: 22px;
            text-align: center;
            color: #0866c6;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Esqueci a senha</h1>

        <p class="descricao">
            Informe o e-mail da sua conta para receber o link de redefinição.
        </p>

        <?php if ($mensagem !== ''): ?>
            <p class="mensagem <?= $erro ? 'erro' : 'ok' ?>" role="status">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <?php if ($linkDev !== ''): ?>
            <p class="dev">
                <strong>Modo desenvolvimento:</strong> sem servidor de e-mail, use este link:<br>
                <a href="<?= htmlspecialchars($linkDev, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($linkDev, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </p>
        <?php endif; ?>

        <form action="" method="POST">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="campo">
                <label for="email">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    maxlength="150"
                    autocomplete="email"
                    value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <button type="submit">Enviar link</button>
        </form>

        <a class="voltar" href="login.php">Voltar para o login</a>
    </main>
</body>
</html>