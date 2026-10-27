
<?php

session_start();
require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Usuario.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensagem = '';
$tipoMensagem = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
        $tipoMensagem = 'erro';
    } else {
        $nomeRecebido = $_POST['nome'] ?? '';
        $emailRecebido = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        if (
            !is_string($nomeRecebido) ||
            !is_string($emailRecebido) ||
            !is_string($senha) ||
            !is_string($confirmarSenha)
        ) {
            $mensagem = 'Os dados enviados são inválidos.';
            $tipoMensagem = 'erro';
        } else {
            $nome = trim($nomeRecebido);
            $email = trim($emailRecebido);

            if (
                $nome === '' ||
                strlen($nome) > 100 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($email) > 150
            ) {
                $mensagem = 'Informe um nome e um e-mail válidos.';
                $tipoMensagem = 'erro';
            } elseif (strlen($senha) < 8 || strlen($senha) > 4096) {
                $mensagem = 'A senha deve ter pelo menos 8 caracteres.';
                $tipoMensagem = 'erro';
            } elseif ($senha !== $confirmarSenha) {
                $mensagem = 'As senhas não coincidem.';
                $tipoMensagem = 'erro';
            } else {
                try {
                    $usuario = new Usuario($pdo);

                    if ($usuario->cadastrar($nome, $email, $senha)) {
                        $mensagem = 'Conta criada com sucesso!';
                        $tipoMensagem = 'sucesso';

                        $nome = '';
                        $email = '';

                        $_SESSION['csrf_token'] =
                            bin2hex(random_bytes(32));
                    } else {
                        $mensagem = 'Este e-mail já está cadastrado.';
                        $tipoMensagem = 'erro';
                    }
                } catch (PDOException $e) {
                    error_log('Erro PDO no cadastro: ' . $e->getMessage());

                    $mensagem =
                        'Não foi possível concluir o cadastro. Tente novamente.';
                    $tipoMensagem = 'erro';
                } catch (RuntimeException $e) {
                    error_log('Erro no cadastro: ' . $e->getMessage());

                    $mensagem =
                        'Não foi possível concluir o cadastro. Tente novamente.';
                    $tipoMensagem = 'erro';
                }
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
            padding: 24px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f3f6fb;
            font-family: Arial, sans-serif;
            color: #173b65;
        }

        .container {
            width: 100%;
            max-width: 460px;
            padding: 30px;
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
            margin-bottom: 24px;
            line-height: 1.4;
        }

        label {
            display: block;
            margin: 18px 0 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 16px;
        }

        input:focus {
            outline: 2px solid #9ac5ee;
            border-color: #1769aa;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 14px;
            border: 0;
            border-radius: 7px;
            background: #1769aa;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #12568d;
        }

        .voltar {
            display: block;
            margin-top: 22px;
            text-align: center;
            color: #1769aa;
        }

        .mensagem {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 7px;
            line-height: 1.4;
        }

        .erro {
            background: #fdecec;
            color: #9b1c1c;
        }

        .sucesso {
            background: #e8f7ec;
            color: #176534;
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
            }

            .container {
                padding: 22px;
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
            class="mensagem <?= $tipoMensagem === 'sucesso' ? 'sucesso' : 'erro' ?>"
            role="status"
        ><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="cadastro.php" method="POST">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION['csrf_token'],
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

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

        <button type="submit">Criar conta</button>
    </form>

    <a class="voltar" href="index.php">Voltar para a página inicial</a>
</main>
</body>
</html>

