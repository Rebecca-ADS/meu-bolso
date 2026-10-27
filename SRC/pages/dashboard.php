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

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$erro = '';

// Exclusão (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $token = $_POST['csrf_token'] ?? '';
    $idExcluir = $_POST['id_transacao'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token) ||
        !is_string($idExcluir) ||
        !ctype_digit($idExcluir)
    ) {
        $erro = 'Requisição inválida.';
    } else {
        try {
            $ok = $transacaoObj->excluir((int) $idExcluir, $idUsuario);
            $_SESSION['flash'] = $ok
                ? 'Lançamento excluído.'
                : 'Lançamento não encontrado.';
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            error_log('Erro ao excluir transação: ' . $e->getMessage());
            $erro = 'Não foi possível excluir agora.';
        }
    }
}

$inicioMes = date('Y-m-01');
$fimMes = date('Y-m-t');

$geral = $transacaoObj->totais($idUsuario);
$mes = $transacaoObj->totais($idUsuario, $inicioMes, $fimMes);
$ultimos = $transacaoObj->listar($idUsuario, 10);

$saldo = $geral['receitas'] - $geral['despesas'];
$percentualDespesas = $mes['receitas'] > 0
    ? ($mes['despesas'] / $mes['receitas']) * 100
    : null;

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
    <title>Dashboard | Meu Bolso</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f4f7fb;
            color: #243047;
            font-family: Arial, sans-serif;
        }

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

        main { max-width: 900px; margin: 32px auto; padding: 0 16px; }

        .aviso { padding: 12px; border-radius: 8px; margin-bottom: 18px; }
        .aviso.ok { color: #0f5132; background: #d1e7dd; }
        .aviso.erro { color: #842029; background: #f8d7da; }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card {
            padding: 20px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px #00000010;
        }

        .card span { display: block; font-size: 14px; color: #65738a; }
        .card strong { display: block; margin-top: 8px; font-size: 24px; }
        .positivo { color: #0f7b3f; }
        .negativo { color: #b42318; }

        .painel {
            padding: 20px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px #00000010;
        }

        .topo-painel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }

        .topo-painel h2 { margin: 0; font-size: 20px; }

        .botao {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 8px;
            background: #1769aa;
            color: #fff;
            text-decoration: none;
        }

        .botao:hover { background: #104d80; }

        .tabela { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #e5eaf2; }
        th { font-size: 13px; color: #65738a; }
        td.valor { white-space: nowrap; font-weight: bold; }
        td a { color: #0866c6; }

        .excluir {
            padding: 6px 10px;
            border: 0;
            border-radius: 6px;
            background: #f8d7da;
            color: #842029;
            cursor: pointer;
        }

        .vazio { padding: 24px 0; text-align: center; color: #65738a; }
    </style>
</head>
<body>
    <header>
        <h1>Meu Bolso</h1>
        <div>
            Olá, <?= e((string) $_SESSION['usuario_nome']) ?> |
            <a href="historico.php">Histórico</a> |
            <a href="logout.php">Sair</a>
        </div>
    </header>

    <main>
        <?php if ($flash !== ''): ?>
            <p class="aviso ok" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <p class="aviso erro" role="alert"><?= e($erro) ?></p>
        <?php endif; ?>

        <section class="cards">
            <div class="card">
                <span>Saldo atual</span>
                <strong class="<?= $saldo >= 0 ? 'positivo' : 'negativo' ?>">
                    <?= e(moeda($saldo)) ?>
                </strong>
            </div>
            <div class="card">
                <span>Receitas do mês</span>
                <strong class="positivo"><?= e(moeda($mes['receitas'])) ?></strong>
            </div>
            <div class="card">
                <span>Despesas do mês</span>
                <strong class="negativo"><?= e(moeda($mes['despesas'])) ?></strong>
            </div>
            <div class="card">
                <span>Despesas / receitas (mês)</span>
                <strong>
                    <?= $percentualDespesas === null
                        ? '—'
                        : e(number_format($percentualDespesas, 1, ',', '.') . '%') ?>
                </strong>
            </div>
        </section>

        <section class="painel">
            <div class="topo-painel">
                <h2>Últimos lançamentos</h2>
                <div>
                    <a class="botao" href="historico.php">Ver histórico</a>
                    <a class="botao" href="nova_transacao.php">+ Novo lançamento</a>
                </div>
            </div>

            <?php if ($ultimos === []): ?>
                <p class="vazio">Nenhum lançamento ainda. Clique em "Novo lançamento" para começar.</p>
            <?php else: ?>
                <div class="tabela">
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Valor</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ultimos as $t): ?>
                            <?php $ehReceita = $t['tipo'] === 'receita'; ?>
                            <tr>
                                <td><?= e(date('d/m/Y', strtotime($t['data_transacao']))) ?></td>
                                <td><?= e($t['descricao']) ?></td>
                                <td><?= e($t['categoria']) ?></td>
                                <td class="valor <?= $ehReceita ? 'positivo' : 'negativo' ?>">
                                    <?= $ehReceita ? '+' : '-' ?> <?= e(moeda((float) $t['valor'])) ?>
                                </td>
                                <td>
                                    <a href="detalhes.php?id=<?= (int) $t['id_transacao'] ?>">Detalhes</a>
                                </td>
                                <td>
                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Excluir este lançamento?');"
                                    >
                                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="id_transacao" value="<?= (int) $t['id_transacao'] ?>">
                                        <button class="excluir" type="submit" name="excluir" value="1">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>