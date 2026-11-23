<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/transacoes.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_login();

$idUsuario = (int) $_SESSION['usuario_id'];

// Mês do gráfico de rosca (padrão: mês atual), no formato AAAA-MM
$mes = $_GET['mes'] ?? date('Y-m');
if (!is_string($mes) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
    $mes = date('Y-m');
}
$inicio = $mes . '-01';
$fim = date('Y-m-t', strtotime($inicio));

// Rosca: despesas por categoria no mês escolhido
$despesas = transacao_despesas_por_categoria($conexao, $idUsuario, $inicio, $fim);
$totalDespesas = 0.0;
$dadosRosca = [['Categoria', 'Valor']];
foreach ($despesas as $d) {
    $totalDespesas += $d['total'];
    $dadosRosca[] = [$d['categoria'], $d['total']];
}

// Colunas: receitas x despesas dos últimos 6 meses
$base = strtotime(date('Y-m-01'));
$meses = [];
for ($i = 5; $i >= 0; $i--) {
    $meses[date('Y-m', strtotime("-$i month", $base))] = ['receitas' => 0.0, 'despesas' => 0.0];
}
$desde = array_key_first($meses) . '-01';

foreach (transacao_resumo_mensal($conexao, $idUsuario, $desde) as $linha) {
    if (isset($meses[$linha['mes']])) {
        $chave = ($linha['tipo'] === 'receita') ? 'receitas' : 'despesas';
        $meses[$linha['mes']][$chave] = $linha['total'];
    }
}

$dadosBarras = [['Mês', 'Receitas', 'Despesas']];
foreach ($meses as $m => $v) {
    $dadosBarras[] = [substr($m, 5, 2) . '/' . substr($m, 0, 4), $v['receitas'], $v['despesas']];
}

// Flags que impedem quebrar o <script> (proteção contra XSS)
$flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

$raiz = '../';
$titulo = 'Gráficos';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Gráficos</h1>

<section class="cartao">
    <form method="GET" action="" class="filtros">
        <div class="campo">
            <label for="mes">Mês das despesas por categoria</label>
            <input type="month" id="mes" name="mes" value="<?= e($mes) ?>" required>
        </div>
        <button type="submit">Atualizar</button>
        <a class="botao neutro" href="graficos.php">Mês atual</a>
    </form>
</section>

<section class="cartao">
    <h2>Despesas por categoria</h2>
    <?php if (count($dadosRosca) > 1): ?>
        <p>Total de despesas no mês: <strong class="negativo"><?= e(moeda($totalDespesas)) ?></strong></p>
        <div id="grafico-rosca" style="width: 100%; height: 380px;"></div>
    <?php else: ?>
        <p class="vazio">Nenhuma despesa registrada neste mês.</p>
    <?php endif; ?>
</section>

<section class="cartao">
    <h2>Receitas x despesas (últimos 6 meses)</h2>
    <div id="grafico-barras" style="width: 100%; height: 380px;"></div>
</section>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', { packages: ['corechart'] });
google.charts.setOnLoadCallback(desenhar);
window.addEventListener('resize', desenhar);

var dadosRosca = <?= json_encode($dadosRosca, $flags) ?>;
var dadosBarras = <?= json_encode($dadosBarras, $flags) ?>;

function desenhar() {
    if (!window.google || !google.visualization) { return; }

    var formato = new google.visualization.NumberFormat({
        prefix: 'R$ ', decimalSymbol: ',', groupingSymbol: '.', fractionDigits: 2
    });

    // Rosca de despesas por categoria
    var divRosca = document.getElementById('grafico-rosca');
    if (divRosca && dadosRosca.length > 1) {
        var tabelaRosca = google.visualization.arrayToDataTable(dadosRosca);
        formato.format(tabelaRosca, 1);
        new google.visualization.PieChart(divRosca).draw(tabelaRosca, {
            pieHole: 0.45,
            pieSliceText: 'percentage',
            sliceVisibilityThreshold: 0,
            legend: { position: 'right' },
            chartArea: { left: 10, top: 10, width: '95%', height: '90%' },
            colors: ['#1769aa', '#2e7d32', '#f4b400', '#c62828', '#8e24aa', '#00897b', '#ef6c00', '#5d4037', '#546e7a', '#d81b60', '#7cb342']
        });
    }

    // Colunas: receitas x despesas
    var tabelaBarras = google.visualization.arrayToDataTable(dadosBarras);
    formato.format(tabelaBarras, 1);
    formato.format(tabelaBarras, 2);
    new google.visualization.ColumnChart(document.getElementById('grafico-barras')).draw(tabelaBarras, {
        colors: ['#0f7b3f', '#b42318'],
        legend: { position: 'top' },
        vAxis: { minValue: 0 },
        chartArea: { left: 70, top: 40, width: '85%', height: '70%' }
    });
}
</script>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>