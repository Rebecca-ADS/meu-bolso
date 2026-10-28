<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once dirname(__DIR__) . '/conecta.php';
require_once dirname(__DIR__) . '/classes/Relatorio.php';
require_once dirname(__DIR__) . '/classes/Transacao.php';

$idUsuario = (int) $_SESSION['usuario_id'];

// Filtro de mês (AAAA-MM). Padrão: mês atual.
$mes = $_GET['mes'] ?? date('Y-m');
if (!is_string($mes) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
    $mes = date('Y-m');
}
$inicio = $mes . '-01';
$fim = date('Y-m-t', strtotime($inicio));

$relatorio = new Relatorio($pdo);
$dados = $relatorio->despesasPorCategoria($idUsuario, $inicio, $fim);

$transacaoObj = new Transacao($pdo);
$totais = $transacaoObj->totais($idUsuario, $inicio, $fim);

$totalDespesas = 0.0;
foreach ($dados as $linha) {
    $totalDespesas += (float) $linha['total'];
}

$labels = array_column($dados, 'categoria');
$valores = array_map(static fn($l) => round((float) $l['total'], 2), $dados);

$percentualDespesas = $totais['receitas'] > 0
    ? ($totais['despesas'] / $totais['receitas']) * 100
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
    <title>Gráficos | Meu Bolso</title>

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

        main { max-width: 900px; margin: 32px auto; padding: 0 16px; }

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

        .filtro { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .filtro label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 14px; }
        .filtro input { min-height: 42px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 16px; }
        .filtro button {
            min-height: 42px; padding: 10px 18px; border: 0; border-radius: 8px;
            background: #1769aa; color: #fff; font-size: 15px; cursor: pointer;
        }
        .filtro button:hover { background: #104d80; }

        .grafico { max-width: 380px; margin: 0 auto 24px; }

        .tabela { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #e5eaf2; }
        th { font-size: 13px; color: #65738a; }

        .vazio { padding: 24px 0; text-align: center; color: #65738a; }
    </style>
</head>
<body>
    <header>
        <h1>Meu Bolso</h1>
        <div>
            <a href="dashboard.php">Dashboard</a> |
            <a href="historico.php">Histórico</a> |
            <a href="logout.php">Sair</a>
        </div>
    </header>

    <main>
        <section class="cards">
            <div class="card">
                <span>Receitas do período</span>
                <strong class="positivo"><?= e(moeda($totais['receitas'])) ?></strong>
            </div>
            <div class="card">
                <span>Despesas do período</span>
                <strong class="negativo"><?= e(moeda($totais['despesas'])) ?></strong>
            </div>
            <div class="card">
                <span>Despesas / receitas</span>
                <strong>
                    <?= $percentualDespesas === null
                        ? '—'
                        : e(number_format($percentualDespesas, 1, ',', '.') . '%') ?>
                </strong>
            </div>
        </section>

        <section class="painel">
            <h2 style="margin-top:0">Gastos por categoria</h2>

            <form class="filtro" method="GET" action="graficos.php">
                <div>
                    <label for="mes">Mês</label>
                    <input type="month" id="mes" name="mes" value="<?= e($mes) ?>">
                </div>
                <button type="submit">Filtrar</button>
            </form>

            <?php if ($dados === []): ?>
                <p class="vazio">Nenhuma despesa registrada neste mês.</p>
            <?php else: ?>
                <div class="grafico">
                    <canvas id="grafico" aria-label="Gráfico de gastos por categoria" role="img"></canvas>
                </div>

                <div class="tabela">
                    <table>
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Total</th>
                                <th>% das despesas</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($dados as $linha): ?>
                            <?php $valor = (float) $linha['total']; ?>
                            <tr>
                                <td><?= e($linha['categoria']) ?></td>
                                <td><?= e(moeda($valor)) ?></td>
                                <td>
                                    <?= $totalDespesas > 0
                                        ? e(number_format(($valor / $totalDespesas) * 100, 1, ',', '.') . '%')
                                        : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php if ($dados !== []): ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
            const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            const valores = <?= json_encode($valores, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

            new Chart(document.getElementById('grafico'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: valores,
                        backgroundColor: [
                            '#1769aa', '#e4572e', '#17bebb', '#ffc914', '#76b041',
                            '#8e5572', '#f28f3b', '#5c6bc0', '#26a69a', '#ef5350', '#78909c'
                        ]
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ' ' + ctx.label + ': R$ ' +
                                    ctx.parsed.toLocaleString('pt-BR', { minimumFractionDigits: 2 })
                            }
                        }
                    }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>