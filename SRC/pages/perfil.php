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
$dados = $usuarioObj->buscarPorId($idUsuario);

if ($dados === null) {
    header('Location: logout.php');
    exit;
}

$nome = $dados['nome'];
$email = $dados['email'];
$mensagem = '';
$erro = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $nomePost = $_POST['nome'] ?? '';
    $emailPost = $_POST['email'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensagem = 'Formulário inválido. Atualize a página e tente novamente.';
        $erro = true;
    } elseif (!is_string($nomePost) || !is_string($emailPost)) {
        $mensagem = 'Dados inválidos.';
        $erro = true;
    } else {
        $nome = trim($nomePost);
        $email = mb_strtolower(trim($emailPost), 'UTF-8');

        if (
            $nome === '' || mb_strlen($nome, 'UTF-8') > 100 ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            mb_strlen($email, 'UTF-8') > 150
        ) {
            $mensagem = 'Confira o nome e o e-mail.';
            $erro = true;
        } else {
            try {
                if ($usuarioObj->atualizarPerfil($idUsuario, $nome, $email)) {
                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $mensagem = 'Perfil atualizado com sucesso!';
                } else {
                    $mensagem = 'Este e-mail já está em uso por outra conta.';
                    $erro = true;
                }
            } catch (Throwable $e) {
                error_log('Erro ao atualizar perfil: ' . $e->getMessage());
                $mensagem = 'Não foi possível salvar agora. Tente novamente.';
                $erro = true;
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
    <title>Perfil | Meu Bolso</title>

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

        .painel { padding: 28px; background: #fff; border-radius: 14px; box-shadow: 0 4px 18px #00000010; }
        h2 { margin: 0 0 20px; }

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
        .info { margin-top: 18px; font-size: 14px; color: #65738a; text-align: center; }
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
            <h2>Meu perfil</h2>

            <?php if ($mensagem !== ''): ?>
                <p class="mensagem <?= $erro ? 'erro' : 'ok' ?>" role="status">
                    <?= e($mensagem) ?>
                </p>
            <?php endif; ?>

            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="campo">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" maxlength="100"
                           value="<?= e($nome) ?>" autocomplete="name" required>
                </div>

                <div class="campo">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" maxlength="150"
                           value="<?= e($email) ?>" autocomplete="email" required>
                </div>

                <button type="submit">Salvar alterações</button>
            </form>

            <p class="info">
                Conta criada em <?= e(date('d/m/Y', strtotime($dados['criado_em']))) ?>
            </p>
        </section>
    </main>
</body>
</html>