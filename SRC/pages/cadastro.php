<?php

declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Usuario.php';

// Cria o token CSRF da sessão.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensagem = '';
$tipoMensagem = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $nomePost = $_POST['nome'] ?? '';
    $emailPost = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    // Valida o token contra ataques CSRF.
    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
        $tipoMensagem = 'erro';
    } elseif (
        !is_string($nomePost) ||
        !is_string($emailPost) ||
        !is_string($senha) ||
        !is_string($confirmarSenha)
    ) {
        // is_string antes do trim, para não dar erro fatal com nome[]=x
        $mensagem = 'Dados inválidos.';
        $tipoMensagem = 'erro';
    } else {
        $nome = trim($nomePost);
        $email = mb_strtolower(trim($emailPost), 'UTF-8');

        if (
            $nome === '' ||
            mb_strlen($nome, 'UTF-8') > 100 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            mb_strlen($email, 'UTF-8') > 150 ||
            strlen($senha) < 8 ||
            strlen($senha) > 4096
        ) {
            $mensagem = 'Confira o nome, o e-mail e a senha. A senha deve ter pelo menos 8 caracteres.';
            $tipoMensagem = 'erro';
        } elseif ($senha !== $confirmarSenha) {
            $mensagem = 'As senhas não coincidem.';
            $tipoMensagem = 'erro';
        } else {
            try {
                $usuarioObj = new Usuario($pdo);

                if ($usuarioObj->cadastrar($nome, $email, $senha)) {
                    // Renova o token e guarda o aviso para o login mostrar.
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $_SESSION['flash_login'] = 'Conta criada com sucesso! Entre com seu e-mail e senha.';

                    header('Location: login.php');
                    exit;
                } else {
                    $mensagem = 'Este e-mail já está cadastrado.';
                    $tipoMensagem = 'erro';
                }
            } catch (PDOException $e) {
                // O detalhe técnico fica no log, não na tela.
                error_log('Erro no cadastro do Meu Bolso: ' . $e->getMessage());

                $mensagem = 'Não foi possível concluir o cadastro. Verifique os dados e tente novamente.';
                $tipoMensagem = 'erro';
            } catch (Throwable $e) {
                error_log('Erro inesperado no cadastro: ' . $e->getMessage());

                $mensagem = 'Ocorreu um erro inesperado. Tente novamente.';
                $tipoMensagem = 'erro';
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

    <title>Criar conta | Meu Bolso</title>

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
        }

        .mensagem.erro {
            color: #842029;
            background: #f8d7da;
        }

        .mensagem.sucesso {
            color: #0f5132;
            background: #d1e7dd;
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

        .voltar {
            display: block;
            margin-top: 22px;
            text-align: center;
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
        <h1>Criar minha conta</h1>

        <p class="descricao">
            Preencha seus dados para começar a organizar suas finanças.
        </p>

        <?php if ($mensagem !== ''): ?>
            <p
                class="mensagem <?= htmlspecialchars($tipoMensagem, ENT_QUOTES, 'UTF-8') ?>"
                role="status"
                aria-live="polite"
            >
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
                <label for="nome">Nome completo</label>
                <input
                    type="text"
                    id="nome"
                    name="nome"
                    maxlength="100"
                    autocomplete="name"
                    value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

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
                <label for="senha">Senha (mínimo de 8 caracteres)</label>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    minlength="8"
                    maxlength="4096"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="campo">
                <label for="confirmar_senha">Confirmar senha</label>
                <input
                    type="password"
                    id="confirmar_senha"
                    name="confirmar_senha"
                    minlength="8"
                    maxlength="4096"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit">Criar conta</button>
        </form>

        <a class="voltar" href="../index.php">
            Voltar para a página inicial
        </a>
    </main>
</body>
</html>

