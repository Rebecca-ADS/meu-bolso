<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Transacao.php';

$idUsuario = (int) $_SESSION['usuario_id'];
$transacaoObj = new Transacao($pdo);

const POR_PAGINA = 15;

// Filtro de mês (formato AAAA-MM). Vazio = todo o período.
$mes = $_GET['mes'] ?? '';
$inicio = null;
$fim = null;

if (!is_string($mes) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
    $mes = '';
} else {
    $inicio = $mes . '-01';
    $fim = date('Y-m-t', strtotime($inicio));
}

$paginaAtual = $_GET['pagina'] ?? '1';
$paginaAtual = (is_string($paginaAtual) && ctype_digit($paginaAtual) && (int) $paginaAtual > 0)
    ? (int) $paginaAtual
    : 1;

$total = $transacaoObj->contarPeriodo($idUsuario, $inicio, $fim);
$totalPaginas = max(1, (int) ceil($total / POR_PAGINA));
$paginaAtual = min($paginaAtual, $totalPaginas);

$lista = $transacaoObj->listarPeriodo(
    $idUsuario,
    $inicio,
    $fim,
    POR_PAGINA,
    ($paginaAtual - 1) * POR_PAGINA
);

$totais = $transacaoObj->totais($idUsuario, $inicio, $fim);
$saldoPeriodo = $totais['receitas'] - $totais['despesas'];

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function moeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function linkPagina(int $pagina, string $mes): string
{
    $params = ['pagina' => $pagina];
    if ($mes !== '') {
        $params['mes'] = $mes;
    }
    return 'historico.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico | Meu Bolso</title>

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

        .aviso { padding: 12px; border-radius: 8px; margin-bottom: 18px; color: #0f5132; background: #d1e7dd; }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .card { padding: 18px; background: #fff; border-radius: 14px; box-shadow: 0 4px 18px #00000010; }
        .card span { display: block; font-size: 14px; color: #65738a; }
        .card strong { display: block; margin-top: 8px; font-size: 22px; }
        .positivo { color: #0f7b3f; }
        .negativo { color: #b42318; }

        .painel { padding: 20px; background: #fff; border-radius: 14px; box-shadow: 0 4px 18px #00000010; }

        .filtro {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .filtro label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 14px; }

        .filtro input {
            min-height: 42px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 16px;
        }

        .botao, .filtro button {
            display: inline-block;
            min-height: 42px;
            padding: 10px 18px;
            border: 0;
            border-radius: 8px;
            background: #1769aa;
            color: #fff;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
        }

        .botao:hover, .filtro button:hover { background: #104d80; }
        .botao.claro { background: #e5eaf2; color: #243047; }

        .tabela { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #e5eaf2; }
        th { font-size: 13px; color: #65738a; }
        td.valor { white-space: nowrap; font-weight: bold; }
        td a { color: #0866c6; }

        .vazio { padding: 24px 0; text-align: center; color: #65738a; }

        .paginacao {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Meu Bolso</h1>
        <div>
            <a href="dashboard.php">Dashboard</a> |
            <a href="graficos.php">Gráficos</a> |
            <a href="perfil.php">Perfil</a> |
            <a href="configuracoes.php">Configurações</a> |
            <a href="logout.php">Sair</a>
        </div>
    </header>

    <main>
        <?php if ($flash !== ''): ?>
            <p class="aviso" role="status"><?= e($flash) ?></p>
        <?php endif; ?>

        <section class="cards">
            <div class="card">
                <span>Receitas</span>
                <strong class="positivo"><?= e(moeda($totais['receitas'])) ?></strong>
            </div>
            <div class="card">
                <span>Despesas</span>
                <strong class="negativo"><?= e(moeda($totais['despesas'])) ?></strong>
            </div>
            <div class="card">
                <span>Saldo do período</span>
                <strong class="<?= $saldoPeriodo >= 0 ? 'positivo' : 'negativo' ?>">
                    <?= e(moeda($saldoPeriodo)) ?>
                </strong>
            </div>
        </section>

        <section class="painel">
            <form class="filtro" method="GET" action="historico.php">
                <div>
                    <label for="mes">Mês</label>
                    <input type="month" id="mes" name="mes" value="<?= e($mes) ?>">
                </div>
                <button type="submit">Filtrar</button>
                <?php if ($mes !== ''): ?>
                    <a class="botao claro" href="historico.php">Ver tudo</a>
                <?php endif; ?>
                <a class="botao" href="nova_transacao.php">+ Novo lançamento</a>
            </form>

            <?php if ($lista === []): ?>
                <p class="vazio">Nenhum lançamento encontrado neste período.</p>
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
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lista as $t): ?>
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
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav class="paginacao" aria-label="Paginação">
                        <?php if ($paginaAtual > 1): ?>
                            <a class="botao claro" href="<?= e(linkPagina($paginaAtual - 1, $mes)) ?>">← Anterior</a>
                        <?php endif; ?>

                        <span>Página <?= $paginaAtual ?> de <?= $totalPaginas ?></span>

                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a class="botao claro" href="<?= e(linkPagina($paginaAtual + 1, $mes)) ?>">Próxima →</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>