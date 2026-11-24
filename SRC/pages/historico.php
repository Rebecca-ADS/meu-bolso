<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/transacoes.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_nivel_exato(NIVEL_USUARIO);

$idUsuario = (int) $_SESSION['usuario_id'];

// Parâmetros de ordenação e filtro (validados por lista fixa)
$permitidas = ['data', 'descricao', 'categoria', 'tipo', 'valor'];

$ordem = $_GET['ordem'] ?? 'data';
$dir = $_GET['dir'] ?? 'desc';
$tipo = $_GET['tipo'] ?? '';

if (!is_string($ordem) || !in_array($ordem, $permitidas, true)) {
    $ordem = 'data';
}
$dir = ($dir === 'asc') ? 'asc' : 'desc';
if (!is_string($tipo) || !in_array($tipo, ['receita', 'despesa'], true)) {
    $tipo = '';
}

$lista = transacao_historico($conexao, $idUsuario, $ordem, $dir, $tipo);

$totalReceitas = 0.0;
$totalDespesas = 0.0;
foreach ($lista as $t) {
    if ($t['tipo'] === 'receita') {
        $totalReceitas += (float) $t['valor'];
    } else {
        $totalDespesas += (float) $t['valor'];
    }
}

// Cabeçalho de coluna clicável: alterna crescente/decrescente
function cabecalho_ordenavel(string $coluna, string $rotulo, string $ordem, string $dir, string $tipo): string
{
    $novaDir = ($ordem === $coluna && $dir === 'asc') ? 'desc' : 'asc';
    $params = ['ordem' => $coluna, 'dir' => $novaDir];
    if ($tipo !== '') {
        $params['tipo'] = $tipo;
    }
    $seta = '';
    if ($ordem === $coluna) {
        $seta = ($dir === 'asc') ? ' ▲' : ' ▼';
    }
    return '<a href="?' . e(http_build_query($params)) . '">' . e($rotulo) . $seta . '</a>';
}

$raiz = '../';
$titulo = 'Histórico';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Histórico de lançamentos</h1>

<section class="cartao">
    <form method="GET" action="" class="filtros">
        <input type="hidden" name="ordem" value="<?= e($ordem) ?>">
        <input type="hidden" name="dir" value="<?= e($dir) ?>">
        <div class="campo">
            <label for="tipo">Filtrar por tipo</label>
            <select id="tipo" name="tipo">
                <option value="" <?= $tipo === '' ? 'selected' : '' ?>>Todos</option>
                <option value="receita" <?= $tipo === 'receita' ? 'selected' : '' ?>>Receitas</option>
                <option value="despesa" <?= $tipo === 'despesa' ? 'selected' : '' ?>>Despesas</option>
            </select>
        </div>
        <button type="submit">Filtrar</button>
        <a class="botao neutro" href="historico.php">Limpar</a>
        <a class="botao" href="nova_transacao.php">+ Novo lançamento</a>
    </form>
</section>

<?php if ($lista === []): ?>
    <p class="vazio">Nenhum lançamento encontrado.</p>
<?php else: ?>
    <div class="tabela">
        <table>
            <thead>
                <tr>
                    <th><?= cabecalho_ordenavel('data', 'Data', $ordem, $dir, $tipo) ?></th>
                    <th><?= cabecalho_ordenavel('descricao', 'Descrição', $ordem, $dir, $tipo) ?></th>
                    <th><?= cabecalho_ordenavel('categoria', 'Categoria', $ordem, $dir, $tipo) ?></th>
                    <th><?= cabecalho_ordenavel('tipo', 'Tipo', $ordem, $dir, $tipo) ?></th>
                    <th><?= cabecalho_ordenavel('valor', 'Valor', $ordem, $dir, $tipo) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lista as $t): ?>
                <?php $ehReceita = ($t['tipo'] === 'receita'); ?>
                <tr>
                    <td><?= e(date('d/m/Y', strtotime($t['data_transacao']))) ?></td>
                    <td><?= e($t['descricao']) ?></td>
                    <td>
                        <?php if (!empty($t['imagem'])): ?>
                            <img class="miniatura pequena" src="../uploads/<?= e($t['imagem']) ?>" alt="">
                        <?php endif; ?>
                        <?= e($t['categoria']) ?>
                    </td>
                    <td><?= $ehReceita ? 'Receita' : 'Despesa' ?></td>
                    <td class="valor <?= $ehReceita ? 'positivo' : 'negativo' ?>">
                        <?= $ehReceita ? '+' : '-' ?> <?= e(moeda($t['valor'])) ?>
                    </td>
                    <td>
                        <a href="detalhes.php?id=<?= (int) $t['id_transacao'] ?>">Detalhes</a> |
                        <a href="editar_transacao.php?id=<?= (int) $t['id_transacao'] ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Totais exibidos</th>
                    <th colspan="2">
                        <span class="positivo">+ <?= e(moeda($totalReceitas)) ?></span><br>
                        <span class="negativo">- <?= e(moeda($totalDespesas)) ?></span>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>