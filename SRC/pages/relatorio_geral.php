<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/ordenacao.php';
require_once dirname(__DIR__) . '/includes/usuarios.php';
require_once dirname(__DIR__) . '/includes/categorias.php';
require_once dirname(__DIR__) . '/includes/imagens.php';
require_once dirname(__DIR__) . '/conecta.php';

// Área exclusiva do gerente (nível 2): consulta de cadastros, sem dados financeiros
exigir_nivel_exato(NIVEL_GERENTE);

// Ordenação de cada tabela (validada por lista fixa)
$ordemUsr = $_GET['ordem_usr'] ?? 'nome';
$dirUsr = $_GET['dir_usr'] ?? 'asc';
if (!is_string($ordemUsr) || !in_array($ordemUsr, ['id', 'nome', 'email', 'nivel'], true)) {
    $ordemUsr = 'nome';
}
$dirUsr = ($dirUsr === 'desc') ? 'desc' : 'asc';

$ordemCat = $_GET['ordem_cat'] ?? 'nome';
$dirCat = $_GET['dir_cat'] ?? 'asc';
if (!is_string($ordemCat) || !in_array($ordemCat, ['nome', 'tipo'], true)) {
    $ordemCat = 'nome';
}
$dirCat = ($dirCat === 'desc') ? 'desc' : 'asc';

$usuarios = usuario_listar($conexao, $ordemUsr, $dirUsr);
$categorias = categoria_listar_com_uso($conexao, $ordemCat, $dirCat);
$imagens = imagem_listar($conexao);

// Contagem de cadastros por nível
$qtdUsuarios = 0;
$qtdGerentes = 0;
$qtdAdmins = 0;
foreach ($usuarios as $u) {
    $nivel = (int) $u['nivel'];
    if ($nivel === NIVEL_ADMIN) {
        $qtdAdmins++;
    } elseif ($nivel === NIVEL_GERENTE) {
        $qtdGerentes++;
    } else {
        $qtdUsuarios++;
    }
}

// Ao ordenar uma tabela, mantém a ordem escolhida na outra
$extrasUsr = ['ordem_cat' => $ordemCat, 'dir_cat' => $dirCat];
$extrasCat = ['ordem_usr' => $ordemUsr, 'dir_usr' => $dirUsr];

$raiz = '../';
$titulo = 'Relatório de cadastros';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Relatório de cadastros</h1>

<section class="cards">
    <div class="card">
        <span>Total de cadastros</span>
        <strong><?= count($usuarios) ?></strong>
    </div>
    <div class="card">
        <span>Usuários</span>
        <strong><?= $qtdUsuarios ?></strong>
    </div>
    <div class="card">
        <span>Gerentes / administradores</span>
        <strong><?= $qtdGerentes ?> / <?= $qtdAdmins ?></strong>
    </div>
    <div class="card">
        <span>Categorias / imagens</span>
        <strong><?= count($categorias) ?> / <?= count($imagens) ?></strong>
    </div>
</section>

<section class="cartao">
    <h2>Pessoas cadastradas</h2>
    <?php if ($usuarios === []): ?>
        <p class="vazio">Nenhum cadastro encontrado.</p>
    <?php else: ?>
        <div class="tabela">
            <table>
                <thead>
                    <tr>
                        <th><?= titulo_ordenavel('id', 'ID', $ordemUsr, $dirUsr, $extrasUsr, 'ordem_usr', 'dir_usr') ?></th>
                        <th><?= titulo_ordenavel('nome', 'Nome', $ordemUsr, $dirUsr, $extrasUsr, 'ordem_usr', 'dir_usr') ?></th>
                        <th><?= titulo_ordenavel('email', 'E-mail', $ordemUsr, $dirUsr, $extrasUsr, 'ordem_usr', 'dir_usr') ?></th>
                        <th><?= titulo_ordenavel('nivel', 'Nível', $ordemUsr, $dirUsr, $extrasUsr, 'ordem_usr', 'dir_usr') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= (int) $u['id_usuario'] ?></td>
                        <td><?= e($u['nome']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e(nome_nivel((int) $u['nivel'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="cartao">
    <h2>Categorias cadastradas</h2>
    <?php if ($categorias === []): ?>
        <p class="vazio">Nenhuma categoria cadastrada.</p>
    <?php else: ?>
        <div class="tabela">
            <table>
                <thead>
                    <tr>
                        <th>Imagem</th>
                        <th><?= titulo_ordenavel('nome', 'Nome', $ordemCat, $dirCat, $extrasCat, 'ordem_cat', 'dir_cat') ?></th>
                        <th><?= titulo_ordenavel('tipo', 'Tipo', $ordemCat, $dirCat, $extrasCat, 'ordem_cat', 'dir_cat') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td>
                            <?php if (!empty($c['imagem'])): ?>
                                <img class="miniatura" src="../uploads/<?= e($c['imagem']) ?>" alt="Imagem da categoria <?= e($c['nome']) ?>">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= e($c['nome']) ?></td>
                        <td><?= $c['tipo'] === 'receita' ? 'Receita' : 'Despesa' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>