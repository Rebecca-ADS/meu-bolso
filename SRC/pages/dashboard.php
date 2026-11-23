<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/transacoes.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_login();

$idUsuario = (int) $_SESSION['usuario_id'];

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$erro = '';

// Exclusão (POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $idExcluir = $_POST['id_transacao'] ?? '';

    if (!csrf_valido($_POST['csrf_token'] ?? null) || !is_string($idExcluir) || !ctype_digit($idExcluir)) {
        $erro = 'Requisição inválida.';
    } else {
        $ok = transacao_excluir($conexao, (int) $idExcluir, $idUsuario);
        $_SESSION['flash'] = $ok ? 'Lançamento excluído.' : 'Lançamento não encontrado.';
        header('Location: dashboard.php');
        exit;
    }
}

$inicioMes = date('Y-m-01');
$fimMes = date('Y-m-t');

$geral = transacao_totais($conexao, $idUsuario);
$mes = transacao_totais($conexao, $idUsuario, $inicioMes, $fimMes);
$ultimos = transacao_listar($conexao, $idUsuario, 10);

$saldo = $geral['receitas'] - $geral['despesas'];
$percentualDespesas = $mes['receitas'] > 0 ? ($mes['despesas'] / $mes['receitas']) * 100 : null;

$raiz = '../';
$titulo = 'Dashboard';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<?php if ($flash !== ''): ?><p class="aviso ok" role="status"><?= e($flash) ?></p><?php endif; ?>
<?php if ($erro !== ''): ?><p class="aviso erro" role="alert"><?= e($erro) ?></p><?php endif; ?>

<p>Olá, <strong><?= e($_SESSION['usuario_nome']) ?></strong>!</p>

<section class="cards">
    <div class="card">
        <span>Saldo atual</span>
        <strong class="<?= $saldo >= 0 ? 'positivo' : 'negativo' ?>"><?= e(moeda($saldo)) ?></strong>
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
        <strong><?= $percentualDespesas === null ? '—' : e(number_format($percentualDespesas, 1, ',', '.') . '%') ?></strong>
    </div>
</section>

<section class="cartao">
    <div class="topo-painel">
        <h2>Últimos lançamentos</h2>
        <div>
            <a class="botao neutro" href="historico.php">Ver histórico</a>
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
                    <?php $ehReceita = ($t['tipo'] === 'receita'); ?>
                    <tr>
                        <td><?= e(date('d/m/Y', strtotime($t['data_transacao']))) ?></td>
                        <td><?= e($t['descricao']) ?></td>
                        <td><?= e($t['categoria']) ?></td>
                        <td class="valor <?= $ehReceita ? 'positivo' : 'negativo' ?>">
                            <?= $ehReceita ? '+' : '-' ?> <?= e(moeda($t['valor'])) ?>
                        </td>
                        <td><a href="detalhes.php?id=<?= (int) $t['id_transacao'] ?>">Detalhes</a></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Excluir este lançamento?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
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
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>