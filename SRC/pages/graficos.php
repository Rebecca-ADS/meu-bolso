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
        .painel h2 { margin-top: 0; }

        .filtro { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .filtro label { display: block; margin-bottom: 6px; font-weight: bold; font-size: 14px; }
        .filtro input { min-height: 42px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 16px; }
        .filtro button {
            min-height: 42px; padding: 10px 18px; border: 0; border-radius: 8px;
            background: #1769aa; color: #fff; font-size: 15px; cursor: pointer;
        }
        .filtro button:hover { background: #104d80; }

        .grafico { position: relative; max-width: 320px; margin: 0 auto 24px; }

        .centro {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            text-align: center;
        }
        .centro small { font-size: 13px; color: #65738a; }
        .centro strong { font-size: 20px; }

        .legenda { list-style: none; margin: 0; padding: 0; }
        .legenda li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 4px;
            border-bottom: 1px solid #e5eaf2;
        }
        .legenda .bolinha { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
        .legenda .nome { flex: 1; }
        .legenda .pct { color: #65738a; font-size: 14px; min-width: 56px; text-align: right; }
        .legenda .valor { font-weight: bold; min-width: 100px; text-align: right; }

        .vazio { padding: 24px 0; text-align: center; color: #65738a; }
    </style>
</head>
<body>
    <header>
        <h1>Meu Bolso</h1>
        <div>
            <a href="dashboard.php">Dashboard</a> |
            <a href="historico.php">Histórico</a> |
            <a href="perfil.php">Perfil</a> |
            <a href="configuracoes.php">Configurações</a> |
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
            <h2>Distribuição das despesas</h2>

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
                    <div class="centro">
                        <small>Total gasto</small>
                        <strong><?= e(moeda($totalDespesas)) ?></strong>
                    </div>
                </div>

                <ul class="legenda">
                    <?php foreach ($dados as $i => $linha): ?>
                        <?php $valor = (float) $linha['total']; ?>
                        <li>
                            <span class="bolinha" data-i="<?= (int) $i ?>"></span>
                            <span class="nome"><?= e($linha['categoria']) ?></span>
                            <span class="pct">
                                <?= $totalDespesas > 0
                                    ? e(number_format(($valor / $totalDespesas) * 100, 1, ',', '.') . '%')
                                    : '—' ?>
                            </span>
                            <span class="valor"><?= e(moeda($valor)) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <?php if ($dados !== []): ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
            const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            const valores = <?= json_encode($valores, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

            const cores = ['#3b82f6', '#f59e0b', '#10b981', '#a855f7', '#ef4444',
                           '#14b8a6', '#f97316', '#6366f1', '#84cc16', '#ec4899', '#64748b'];

            document.querySelectorAll('.bolinha').forEach((el) => {
                el.style.background = cores[Number(el.dataset.i) % cores.length];
            });

            new Chart(document.getElementById('grafico'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: valores, backgroundColor: cores, borderWidth: 2 }]
                },
                options: {
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0
                                        ? (ctx.parsed / total * 100).toFixed(1).replace('.', ',')
                                        : '0';
                                    return ' ' + ctx.label + ': R$ ' +
                                        ctx.parsed.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) +
                                        ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>