<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Transacao.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idUsuario = (int) $_SESSION['usuario_id'];
$transacaoObj = new Transacao($pdo);
$erro = '';

// Exclusão (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $token = $_POST['csrf_token'] ?? '';
    $idPost = $_POST['id_transacao'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token) ||
        !is_string($idPost) ||
        !ctype_digit($idPost)
    ) {
        $erro = 'Requisição inválida.';
    } else {
        try {
            $ok = $transacaoObj->excluir((int) $idPost, $idUsuario);
            $_SESSION['flash'] = $ok ? 'Lançamento excluído.' : 'Lançamento não encontrado.';
            header('Location: historico.php');
            exit;
        } catch (Throwable $e) {
            error_log('Erro ao excluir transação: ' . $e->getMessage());
            $erro = 'Não foi possível excluir agora.';
        }
    }
}

$id = $_GET['id'] ?? '';
$transacao = (is_string($id) && ctype_digit($id))
    ? $transacaoObj->buscar((int) $id, $idUsuario)
    : null;

if ($transacao === null && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(404);
}

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function moeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes | Meu Bolso</title>

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
            max-width: 480px;
            padding: 32px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08);
        }

        h1 { margin: 0 0 20px; text-align: center; font-size: 28px; }

        .mensagem { margin-bottom: 18px; padding: 12px; border-radius: 8px; color: #842029; background: #f8d7da; }

        .valor { text-align: center; font-size: 32px; font-weight: bold; margin-bottom: 24px; }
        .positivo { color: #0f7b3f; }
        .negativo { color: #b42318; }

        dl { margin: 0 0 24px; }
        dt { font-size: 13px; color: #65738a; margin-top: 14px; }
        dd { margin: 4px 0 0; font-size: 17px; }

        .acoes { display: flex; gap: 12px; flex-wrap: wrap; }

        .botao, button {
            flex: 1;
            min-height: 44px;
            padding: 10px 16px;
            border: 0;
            border-radius: 7px;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        .botao { background: #1769aa; color: #fff; line-height: 24px; }
        .botao:hover { background: #104d80; }
        button.excluir { background: #f8d7da; color: #842029; }
        form { flex: 1; display: flex; }
        form button { width: 100%; }
    </style>
</head>
<body>
    <main class="container">
        <?php if ($erro !== ''): ?>
            <p class="mensagem" role="alert"><?= e($erro) ?></p>
        <?php endif; ?>

        <?php if ($transacao === null): ?>
            <h1>Lançamento não encontrado</h1>
            <div class="acoes">
                <a class="botao" href="historico.php">Voltar ao histórico</a>
            </div>
        <?php else: ?>
            <?php $ehReceita = $transacao['tipo'] === 'receita'; ?>

            <h1>Detalhes do lançamento</h1>

            <p class="valor <?= $ehReceita ? 'positivo' : 'negativo' ?>">
                <?= $ehReceita ? '+' : '-' ?> <?= e(moeda((float) $transacao['valor'])) ?>
            </p>

            <dl>
                <dt>Descrição</dt>
                <dd><?= e($transacao['descricao']) ?></dd>

                <dt>Tipo</dt>
                <dd><?= $ehReceita ? 'Receita' : 'Despesa' ?></dd>

                <dt>Categoria</dt>
                <dd><?= e($transacao['categoria']) ?></dd>

                <dt>Data</dt>
                <dd><?= e(date('d/m/Y', strtotime($transacao['data_transacao']))) ?></dd>

                <dt>Registrado em</dt>
                <dd><?= e(date('d/m/Y H:i', strtotime($transacao['criado_em']))) ?></dd>
            </dl>

            <div class="acoes">
                <a class="botao" href="historico.php">Voltar</a>

                <form method="POST" onsubmit="return confirm('Excluir este lançamento?');">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id_transacao" value="<?= (int) $transacao['id_transacao'] ?>">
                    <button class="excluir" type="submit" name="excluir" value="1">Excluir</button>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>