<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Usuario.php';

// Se já estiver logado, vai direto para o dashboard.
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensagem = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $emailPost = $_POST['email'] ?? '';
    $senhaPost = $_POST['senha'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
    } elseif (!is_string($emailPost) || !is_string($senhaPost)) {
        $mensagem = 'Dados inválidos.';
    } else {
        $email = mb_strtolower(trim($emailPost), 'UTF-8');

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $senhaPost === '' ||
            strlen($senhaPost) > 4096
        ) {
            // Mensagem genérica de propósito.
            $mensagem = 'E-mail ou senha incorretos.';
        } else {
            try {
                $usuarioObj = new Usuario($pdo);
                $dados = $usuarioObj->autenticar($email, $senhaPost);

                if ($dados !== null) {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = (int) $dados['id_usuario'];
                    $_SESSION['usuario_nome'] = $dados['nome'];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    header('Location: dashboard.php');
                    exit;
                }

                $mensagem = 'E-mail ou senha incorretos.';
            } catch (Throwable $e) {
                error_log('Erro no login do Meu Bolso: ' . $e->getMessage());
                $mensagem = 'Não foi possível entrar agora. Tente novamente.';
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
    <title>Entrar | Meu Bolso</title>

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
            font-size: 30px;
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
            color: #842029;
            background: #f8d7da;
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
            margin-top: 6px;
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

        .links {
            margin-top: 22px;
            text-align: center;
            line-height: 1.8;
        }

        .links a {
            color: #0866c6;
        }

        @media (max-width: 480px) {
            .container {
                padding: 24px 20px;
            }

            h1 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Entrar</h1>

        <p class="descricao">Acesse sua conta para ver suas finanças.</p>

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

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    maxlength="4096"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit">Entrar</button>
        </form>

        <div class="links">
          <a href="cadastro.php">Criar uma conta</a><br>
        <a href="../index.php">Voltar para a página inicial</a> 
        </div>
    </main>
</body>
</html>