# Checklist de teste - Meu Bolso

___________________   Data: ___/___/_____

Antes de começar: siga o README (instalação) do zero, em um computador com XAMPP.
Marque [x] quando o resultado for o esperado. Anote o problema na coluna Observação.

## A. Instalação

| # | O que fazer | Resultado esperado | OK | Observação |
|---|---|---|---|---|
| 1 | Importar database/meu-bolso.sql no phpMyAdmin | Banco meu_bolso criado com 4 tabelas | [ ] | |
| 2 | Criar SRC/conecta.php a partir do conecta.example.php | Sem erro de conexão ao abrir o sistema | [ ] | |
| 3 | Abrir http://localhost/meu-bolso/SRC/index.php | Página inicial abre com o botão de entrar | [ ] | |

## B. Acesso e níveis

| # | O que fazer | Resultado esperado | OK | Observação |
|---|---|---|---|---|
| 4 | Entrar com admin@meubolso.com / Admin@2026 | Abre a tela Usuários; menu: Usuários, Categorias, Imagens, Perfil | [ ] | |
| 5 | Sair e entrar com gerente@meubolso.com / Gerente@2026 | Abre o Relatório de cadastros, sem valores financeiros | [ ] | |
| 6 | Sair e entrar com usuario@meubolso.com / Usuario@2026 | Abre o Dashboard; menu: Dashboard, Histórico, Gráficos, Perfil | [ ] | |
| 7 | Logado como usuário, abrir .../pages/usuarios.php | Mensagem "Acesso negado" | [ ] | |
| 8 | Entrar com senha errada | Mensagem de erro, sem entrar | [ ] | |
| 9 | Clicar em Sair | Volta ao login; botão Voltar do navegador não reabre a área restrita | [ ] | |

## C. Cadastro de conta

| # | O que fazer | Resultado esperado | OK | Observação |
|---|---|---|---|---|
| 10 | Criar conta nova (nome, e-mail, senha com 8+ caracteres) | Conta criada com nível Usuário; consegue entrar | [ ] | |
| 11 | Criar conta com e-mail já existente | Mensagem de erro | [ ] | |

## D. Usuário: lançamentos

| # | O que fazer | Resultado esperado | OK | Observação |
|---|---|---|---|---|
| 12 | Novo lançamento: despesa, categoria Alimentação, valor 50,00 | Aparece no Dashboard e no Histórico; saldo e despesas atualizam | [ ] | |
| 13 | Novo lançamento: receita, categoria Salário | Receita aparece e soma no saldo | [ ] | |
| 14 | Histórico: clicar nos títulos das colunas | A lista reordena (crescente e decrescente) | [ ] | |
| 15 | Clicar em Detalhes de um lançamento | Mostra os dados completos | [ ] | |
| 16 | Clicar em Editar, mudar o valor e salvar | Mensagem de sucesso; valor novo na lista | [ ] | |
| 17 | Em Editar, clicar em Cancelar | Volta ao Histórico sem alterar | [ ] | |
| 18 | Excluir um lançamento | Some da lista; totais recalculam | [ ] | |
| 19 | Abrir Gráficos (com internet) | Aparecem o gráfico de pizza e o de colunas | [ ] | |
| 20 | Perfil: mudar o nome | Nome atualizado no cabeçalho | [ ] | |

## E. Gerente

| # | O que fazer | Resultado esperado | OK | Observação |
|---|---|---|---|---|
| 21 | No Relatório de cadastros, ordenar a tabela de usuários por Nome e por Nível | Tabela reordena | [ ] | |
| 22 | Procurar valores de dinheiro na tela | Nenhum valor financeiro aparece | [ ] | |

## F. Administrador

| # | O que fazer | Resultado esperado | OK | Observação |
|---|---|---|---|---|
| 23 | Usuários: cadastrar um usuário novo (nível Gerente) | Aparece na lista | [ ] | |
| 24 | Usuários: mudar o nível de uma conta e salvar | Mensagem "Nível atualizado" | [ ] | |
| 25 | Usuários: excluir uma conta sem lançamentos | Conta removida | [ ] | |
| 26 | Usuários: excluir uma conta com lançamentos | Mensagem informando que não é possível | [ ] | |
| 27 | Imagens: enviar uma imagem JPG ou PNG | Aparece na tabela com miniatura | [ ] | |
| 28 | Imagens: tentar enviar um arquivo .pdf | Mensagem de erro, arquivo recusado | [ ] | |
| 29 | Imagens: editar o título | Título atualizado | [ ] | |
| 30 | Imagens: excluir uma imagem | Some da lista | [ ] | |
| 31 | Categorias: ligar uma imagem a Alimentação | Miniatura aparece na categoria | [ ] | |
| 32 | Entrar como usuário e abrir o Histórico | A miniatura aparece nos lançamentos dessa categoria | [ ] | |
| 33 | Categorias: cadastrar categoria nova | Aparece na lista | [ ] | |
| 34 | Categorias: excluir categoria sem lançamentos | Removida; categoria em uso não tem botão de excluir | [ ] | |

## Defeitos encontrados

| # | Tela | O que aconteceu | Como repetir |
|---|---|---|---|
| | | | |
