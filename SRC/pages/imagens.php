<?php

require_once dirname(__DIR__) . '/includes/funcoes.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/ordenacao.php';
require_once dirname(__DIR__) . '/includes/upload.php';
require_once dirname(__DIR__) . '/includes/imagens.php';
require_once dirname(__DIR__) . '/conecta.php';

// Área exclusiva do administrador (nível 3)
exigir_nivel_exato(NIVEL_ADMIN);

$idUsuario = (int) $_SESSION['usuario_id'];
$pastaUploads = dirname(__DIR__) . '/uploads/';

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$erro = '';
$tituloImg = '';
$idEditar = 0;
$tituloEdicao = null;

// Ordenação da lista (validada por lista fixa)
$ordem = $_GET['ordem'] ?? 'criado';
$dir = $_GET['dir'] ?? 'desc';
if (!is_string($ordem) || !in_array($ordem, ['titulo', 'criado', 'uso'], true)) {
    $ordem = 'criado';
}
$dir = ($dir === 'asc') ? 'asc' : 'desc';

// Imagem em edição (?editar=ID)
if (isset($_GET['editar']) && is_string($_GET['editar']) && ctype_digit($_GET['editar'])) {
    $idEditar = (int) $_GET['editar'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $erro = 'Formulário inválido (ou arquivo maior que o limite do servidor). Tente novamente.';

    } elseif (isset($_POST['excluir'])) {
        // ---- CRUD - DELETE: remove o registro e apaga o arquivo da pasta uploads ----
        $id = $_POST['id_imagem'] ?? '';
        if (!is_string($id) || !ctype_digit($id)) {
            $erro = 'Requisição inválida.';
        } else {
            $img = imagem_buscar($conexao, (int) $id);
            if ($img !== null && imagem_excluir($conexao, (int) $id)) {
                upload_remover($pastaUploads, $img['arquivo']);
                $_SESSION['flash'] = 'Imagem removida.';
            } else {
                $_SESSION['flash'] = 'Imagem não encontrada.';
            }
            header('Location: imagens.php');
            exit;
        }

    } elseif (isset($_POST['atualizar'])) {
        // ---- CRUD - UPDATE: edita o título e/ou troca o arquivo da imagem ----
        $id = $_POST['id_imagem'] ?? '';
        $tituloNovo = $_POST['titulo'] ?? '';
        $arq = $_FILES['arquivo'] ?? null;

        if (!is_string($id) || !ctype_digit($id) || !is_string($tituloNovo)) {
            $erro = 'Requisição inválida.';
        } else {
            $idEditar = (int) $id;
            $tituloEdicao = trim($tituloNovo);
            $img = imagem_buscar($conexao, $idEditar);
            $arquivoNovo = null;

            if ($img === null) {
                $erro = 'Imagem não encontrada.';
            } elseif ($tituloEdicao === '' || mb_strlen($tituloEdicao, 'UTF-8') > 100) {
                $erro = 'Informe um título (até 100 caracteres).';
            } else {
                $enviou = is_array($arq) && ($arq['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
                if ($enviou) {
                    [$arquivoNovo, $msg] = upload_imagem($arq, $pastaUploads);
                    if ($arquivoNovo === null) {
                        $erro = $msg;
                    }
                }

                if ($erro === '') {
                    $arquivoFinal = $arquivoNovo ?? $img['arquivo'];
                    if (imagem_atualizar($conexao, $idEditar, $tituloEdicao, $arquivoFinal)) {
                        if ($arquivoNovo !== null) {
                            upload_remover($pastaUploads, $img['arquivo']);
                        }
                        $_SESSION['flash'] = 'Imagem atualizada.';
                        header('Location: imagens.php');
                        exit;
                    }
                    upload_remover($pastaUploads, $arquivoNovo);
                    $erro = 'Não foi possível salvar agora.';
                }
            }
        }

    } else {
        // ---- CRUD - CREATE: upload da imagem (valida formato e tamanho) e grava no banco ----
        $tituloImg = $_POST['titulo'] ?? '';
        $arq = $_FILES['arquivo'] ?? null;

        if (!is_string($tituloImg)) {
            $erro = 'Dados inválidos.';
        } else {
            $tituloImg = trim($tituloImg);
            if ($tituloImg === '' || mb_strlen($tituloImg, 'UTF-8') > 100) {
                $erro = 'Informe um título (até 100 caracteres).';
            } else {
                [$arquivoNovo, $msg] = upload_imagem($arq, $pastaUploads);
                if ($arquivoNovo === null) {
                    $erro = $msg;
                } elseif (imagem_salvar($conexao, $idUsuario, $tituloImg, $arquivoNovo)) {
                    $_SESSION['flash'] = 'Imagem enviada com sucesso.';
                    header('Location: imagens.php');
                    exit;
                } else {
                    upload_remover($pastaUploads, $arquivoNovo);
                    $erro = 'Não foi possível salvar a imagem agora.';
                }
            }
        }
    }
}

$imgEdicao = $idEditar > 0 ? imagem_buscar($conexao, $idEditar) : null;
if ($imgEdicao !== null && $tituloEdicao === null) {
    $tituloEdicao = $imgEdicao['titulo'];
}

// CRUD - READ: lista as imagens (tabela ordenável)
$imagens = imagem_listar($conexao, $ordem, $dir);

$raiz = '../';
$titulo = 'Imagens';
require dirname(__DIR__) . '/includes/cabecalho.php';
?>
<h1>Imagens do sistema</h1>

<?php if ($flash !== ''): ?><p class="aviso ok" role="status"><?= e($flash) ?></p><?php endif; ?>
<?php if ($erro !== ''): ?><p class="aviso erro" role="alert"><?= e($erro) ?></p><?php endif; ?>

<?php if ($imgEdicao !== null): ?>
<section class="cartao">
    <h2>Editar imagem</h2>
    <form method="POST" action="imagens.php?editar=<?= (int) $imgEdicao['id_imagem'] ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id_imagem" value="<?= (int) $imgEdicao['id_imagem'] ?>">

        <p>Imagem atual:<br>
            <img class="miniatura grande" src="../uploads/<?= e($imgEdicao['arquivo']) ?>" alt="<?= e($imgEdicao['titulo']) ?>">
        </p>

        <div class="campo">
            <label for="titulo_ed">Título</label>
            <input type="text" id="titulo_ed" name="titulo" maxlength="100" value="<?= e($tituloEdicao) ?>" required>
        </div>

        <div class="campo">
            <label for="arquivo_ed">Trocar o arquivo (opcional - JPG, PNG ou WEBP, até 2 MB)</label>
            <input type="file" id="arquivo_ed" name="arquivo" accept="image/jpeg,image/png,image/webp">
        </div>

        <button type="submit" name="atualizar" value="1">Salvar alterações</button>
        <a class="botao neutro" href="imagens.php">Cancelar</a>
    </form>
</section>
<?php endif; ?>

<section class="cartao">
    <h2>Enviar nova imagem</h2>
    <form method="POST" action="imagens.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="campo">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" maxlength="100" value="<?= e($tituloImg) ?>" required>
        </div>

        <div class="campo">
            <label for="arquivo">Arquivo (JPG, PNG ou WEBP, até 2 MB)</label>
            <input type="file" id="arquivo" name="arquivo" accept="image/jpeg,image/png,image/webp" required>
        </div>

        <button type="submit">Enviar imagem</button>
    </form>
</section>

<?php if ($imagens === []): ?>
    <p class="vazio">Nenhuma imagem enviada ainda.</p>
<?php else: ?>
    <div class="tabela">
        <table>
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th><?= titulo_ordenavel('titulo', 'Título', $ordem, $dir) ?></th>
                    <th><?= titulo_ordenavel('criado', 'Enviada em', $ordem, $dir) ?></th>
                    <th><?= titulo_ordenavel('uso', 'Categorias que usam', $ordem, $dir) ?></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($imagens as $im): ?>
                <tr>
                    <td><img class="miniatura" src="../uploads/<?= e($im['arquivo']) ?>" alt="<?= e($im['titulo']) ?>"></td>
                    <td><?= e($im['titulo']) ?></td>
                    <td><?= e(date('d/m/Y', strtotime($im['criado_em']))) ?></td>
                    <td><?= (int) $im['uso'] ?></td>
                    <td><a href="imagens.php?editar=<?= (int) $im['id_imagem'] ?>">Editar</a></td>
                    <td>
                        <form method="POST" action="imagens.php" onsubmit="return confirm('Remover esta imagem?');">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id_imagem" value="<?= (int) $im['id_imagem'] ?>">
                            <button class="excluir" type="submit" name="excluir" value="1">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require dirname(__DIR__) . '/includes/rodape.php'; ?>