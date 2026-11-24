<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/usuarios.php';
require_once dirname(__DIR__) . '/includes/ordenacao.php';
require_once dirname(__DIR__) . '/conecta.php';

// Só o administrador (nível 3) acessa esta página
exigir_nivel(NIVEL_ADMIN);

$idLogado = (int) $_SESSION['usuario_id'];

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$erro = '';
$nomeNovo = '';
$emailNovo = '';
$nivelNovo = NIVEL_USUARIO;

// Ordenação (lista fixa)
$ordem = $_GET['ordem'] ?? 'nome';
$dir = $_GET['dir'] ?? 'asc';
if (!is_string($ordem) || !in_array($ordem, ['id', 'nome', 'email', 'nivel'], true)) {
    $ordem = 'nome';
}
$dir = ($dir === 'desc') ? 'desc' : 'asc';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $erro = 'Formulário inválido. Atualize a página e tente novamente.';

    } elseif ($acao === 'cadastrar') {
        $nomeNovo = $_POST['nome'] ?? '';
        $emailNovo = $_POST['email'] ?? '';
        $senhaNova = $_POST['senha'] ?? '';
        $nivelNovo = $_POST['nivel'] ?? '';

        if (!is_string($nomeNovo) || !is_string($emailNovo) || !is_string($senhaNova) || !is_string($nivelNovo)) {
            $erro = 'Dados inválidos.';
            $nomeNovo = $emailNovo = '';
            $nivelNovo = NIVEL_USUARIO;
        } else {
            $nomeNovo = trim($nomeNovo);
            $emailNovo = mb_strtolower(trim($emailNovo), 'UTF-8');

            if (!in_array($nivelNovo, ['1', '2', '3'], true)) {
                $erro = 'Escolha um nível válido.';
            } elseif (
                $nomeNovo === '' || mb_strlen($nomeNovo, 'UTF-8') > 100 ||
                !filter_var($emailNovo, FILTER_VALIDATE_EMAIL) || mb_strlen($emailNovo, 'UTF-8') > 150 ||
                strlen($senhaNova) < 8 || strlen($senhaNova) > 4096
            ) {
                $erro = 'Confira o nome, o e-mail e a senha (mínimo de 8 caracteres).';
            } elseif (usuario_cadastrar($conexao, $nomeNovo, $emailNovo, $senhaNova, (int) $nivelNovo)) {
                $_SESSION['flash'] = 'Usuário cadastrado com sucesso.';
                header('Location: usuarios.php');
                exit;
            } else {
                $erro = 'Este e-mail já está cadastrado.';
            }
        }

    } elseif ($acao === 'nivel' || $acao === 'excluir') {
        $idAlvo = $_POST['id_usuario'] ?? '';

        if (!is_string($idAlvo) || !ctype_digit($idAlvo)) {
            $erro = 'Requisição inválida.';
        } elseif ((int) $idAlvo === $idLogado) {
            $erro = 'Você não pode alterar nem excluir a sua própria conta aqui.';
        } elseif ($acao === 'nivel') {
            $nivel = $_POST['nivel'] ?? '';
            if (!is_string($nivel) || !in_array($nivel, ['1', '2', '3'], true)) {
                $erro = 'Escolha um nível válido.';
            } elseif (usuario_alterar_nivel($conexao, (int) $idAlvo, (int) $nivel)) {
                $_SESSION['flash'] = 'Nível atualizado.';
                header('Location: usuarios.php');
                exit;
            } else {
                $erro = 'Não foi possível atualizar o nível.';
            }
        } else {
            if (usuario_excluir($conexao, (int) $idAlvo)) {
                $_SESSION['flash'] = 'Usuário excluído.';
                header('Location: usuarios.php');
                exit;
            }
            $erro = 'Não foi possível excluir: este usuário possui lançamentos ou outros dados vinculados.';
        }
    } else {
        $erro = 'Ação inválida.';
    }
}

$usuarios = usuario_listar($conexao, $ordem, $dir);

$raiz = '../';
$titulo = 'Usuários';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Gerenciar usuários</h1>

<?php if ($flash !== ''): ?><p class="aviso ok" role="status"><?= e($flash) ?></p><?php endif; ?>
<?php if ($erro !== ''): ?><p class="aviso erro" role="alert"><?= e($erro) ?></p><?php endif; ?>

<section class="cartao">
    <h2>Cadastrar usuário</h2>
    <form method="POST" action="" class="filtros">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="acao" value="cadastrar">

        <div class="campo">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" maxlength="100" value="<?= e($nomeNovo) ?>" required>
        </div>
        <div class="campo">
            <label for="email">E-mail (login)</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= e($emailNovo) ?>" required>
        </div>
        <div class="campo">
            <label for="senha">Senha (mín. 8)</label>
            <input type="password" id="senha" name="senha" minlength="8" maxlength="4096" required>
        </div>
        <div class="campo">
            <label for="nivel">Nível</label>
            <select id="nivel" name="nivel">
                <?php foreach ([1, 2, 3] as $n): ?>
                    <option value="<?= $n ?>" <?= (string) $nivelNovo === (string) $n ? 'selected' : '' ?>>
                        <?= e($n . ' - ' . nome_nivel($n)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Cadastrar</button>
    </form>
</section>

<div class="tabela">
    <table>
        <thead>
            <tr>
                <th><?= titulo_ordenavel('id', 'Id', $ordem, $dir) ?></th>
                <th><?= titulo_ordenavel('nome', 'Nome', $ordem, $dir) ?></th>
                <th><?= titulo_ordenavel('email', 'E-mail', $ordem, $dir) ?></th>
                <th><?= titulo_ordenavel('nivel', 'Nível', $ordem, $dir) ?></th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <?php $ehVoce = ((int) $u['id_usuario'] === $idLogado); ?>
            <tr>
                <td><?= (int) $u['id_usuario'] ?></td>
                <td><?= e($u['nome']) ?><?= $ehVoce ? ' (você)' : '' ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['nivel'] . ' - ' . nome_nivel((int) $u['nivel'])) ?></td>
                <td>
                    <?php if ($ehVoce): ?>
                        —
                    <?php else: ?>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                            <form method="POST" style="display: flex; gap: 6px; margin: 0;">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="acao" value="nivel">
                                <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                                <select name="nivel" aria-label="Nível">
                                    <?php foreach ([1, 2, 3] as $n): ?>
                                        <option value="<?= $n ?>" <?= (int) $u['nivel'] === $n ? 'selected' : '' ?>>
                                            <?= e($n . ' - ' . nome_nivel($n)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Salvar</button>
                            </form>
                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Excluir este usuário?');">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id_usuario" value="<?= (int) $u['id_usuario'] ?>">
                                <button class="excluir" type="submit">Excluir</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>
