<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/transacoes.php';
require_once dirname(__DIR__) . '/conecta.php';

exigir_nivel_exato(NIVEL_USUARIO);

$idUsuario = (int) $_SESSION['usuario_id'];

$id = $_GET['id'] ?? '';
$lancamento = null;

if (is_string($id) && ctype_digit($id)) {
    $lancamento = transacao_buscar($conexao, (int) $id, $idUsuario);
}

$raiz = '../';
$titulo = 'Detalhes do lançamento';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Detalhes do lançamento</h1>

<?php if ($lancamento === null): ?>
    <p class="aviso erro" role="alert">Lançamento não encontrado.</p>
    <a class="botao neutro" href="historico.php">Voltar ao histórico</a>
<?php else: ?>
    <?php $ehReceita = ($lancamento['tipo'] === 'receita'); ?>
    <section class="cartao">
        <div class="tabela">
            <table>
                <tbody>
                    <tr><th>Descrição</th><td><?= e($lancamento['descricao']) ?></td></tr>
                    <tr><th>Tipo</th><td><?= $ehReceita ? 'Receita' : 'Despesa' ?></td></tr>
                    <tr><th>Categoria</th><td><?= e($lancamento['categoria']) ?></td></tr>
                    <tr>
                        <th>Valor</th>
                        <td class="valor <?= $ehReceita ? 'positivo' : 'negativo' ?>">
                            <?= $ehReceita ? '+' : '-' ?> <?= e(moeda($lancamento['valor'])) ?>
                        </td>
                    </tr>
                    <tr><th>Data</th><td><?= e(date('d/m/Y', strtotime($lancamento['data_transacao']))) ?></td></tr>
                    <tr><th>Registrado em</th><td><?= e(date('d/m/Y H:i', strtotime($lancamento['criado_em']))) ?></td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <a class="botao" href="editar_transacao.php?id=<?= (int) $lancamento['id_transacao'] ?>">Editar</a>
    <a class="botao neutro" href="historico.php">Voltar ao histórico</a>
<?php endif; ?>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>