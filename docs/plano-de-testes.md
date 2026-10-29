# Plano de Testes - Meu Bolso

Ambiente: XAMPP (Apache + MySQL), PHP 8, navegador Edge/Chrome.
Contas de teste: `conta.a@teste.com` e `conta.b@teste.com` (fictícias).

## 1. Autenticação (RF01, RF02, RN01, RN-S01, RN-S04)

| ID | Caso de teste | Resultado esperado | Resultado obtido |
|---|---|---|---|
| CT01 | Cadastrar com dados válidos | Redireciona ao login com aviso de sucesso | |
| CT02 | Cadastrar com e-mail já existente | "Este e-mail já está cadastrado." | |
| CT03 | Cadastrar com senha de menos de 8 caracteres | Mensagem de erro | |
| CT04 | Cadastrar com senhas diferentes | "As senhas não coincidem." | |
| CT05 | Login com dados corretos | Abre o dashboard | |
| CT06 | Login com senha errada | "E-mail ou senha incorretos." | |
| CT07 | Login com e-mail inexistente | Mesma mensagem do CT06 | |
| CT08 | Logout | Encerra a sessão e volta à página inicial | |
| CT09 | Abrir páginas privadas sem login | Redireciona ao login | |

## 2. Lançamentos (RF04 a RF08, RN03 a RN08)

| ID | Caso de teste | Resultado esperado | Resultado obtido |
|---|---|---|---|
| CT10 | Cadastrar receita válida | Aparece no dashboard; saldo aumenta | |
| CT11 | Cadastrar despesa válida | Aparece no dashboard; saldo diminui | |
| CT12 | Valor zero | Mensagem de erro | |
| CT13 | Valor negativo | Mensagem de erro | |
| CT14 | Descrição vazia | Mensagem de erro | |
| CT15 | Categoria incompatível com o tipo | Mensagem de erro | |
| CT16 | Ver detalhes de um lançamento | Mostra descrição, tipo, categoria, data e valor | |
| CT17 | Excluir com confirmação | Remove o lançamento; saldo e gráfico atualizam | |
| CT18 | Cancelar a confirmação de exclusão | Nada é excluído | |
| CT19 | Filtrar histórico por mês | Lista e totais do mês | |

## 3. Gráficos (RF09, RN10)

| ID | Caso de teste | Resultado esperado | Resultado obtido |
|---|---|---|---|
| CT20 | Mês com despesas | Rosca e tabela com percentuais | |
| CT21 | Mês sem despesas | "Nenhuma despesa registrada neste mês." | |
| CT22 | Mês sem receitas | Percentual exibido como "—" (sem divisão por zero) | |

## 4. Segurança e isolamento (RN-S02, RNF-S04, RNF-S06, RNF-S07)

| ID | Caso de teste | Resultado esperado | Resultado obtido |
|---|---|---|---|
| CT23 | Conta B abre `detalhes.php?id=` de um lançamento da conta A | "Lançamento não encontrado" | **Passou** |
| CT24 | Dashboard da conta B | Sem dados da conta A | |
| CT25 | Histórico e gráfico da conta B | Sem dados da conta A | |
| CT26 | Enviar formulário com token CSRF inválido | "Formulário inválido." | |
| CT27 | Descrição com `<script>alert(1)</script>` | Texto exibido sem executar | |
| CT28 | Senha no banco | Hash começando com `$2y$` | |

## 5. Perfil e configurações (RF10, RF11)

| ID | Caso de teste | Resultado esperado | Resultado obtido |
|---|---|---|---|
| CT29 | Alterar o nome | "Olá, ..." do menu muda | |
| CT30 | Usar e-mail de outra conta | "Este e-mail já está em uso" | |
| CT31 | Alterar senha com a atual errada | "A senha atual está incorreta." | |
| CT32 | Alterar senha corretamente | Login funciona com a nova senha | |
| CT33 | Excluir conta com senha errada | Conta não é excluída | |
| CT34 | Excluir conta com senha correta | Conta e lançamentos removidos | |

## 6. Recuperação de senha (RF13)

| ID | Caso de teste | Resultado esperado | Resultado obtido |
|---|---|---|---|
| CT35 | Pedir link com e-mail cadastrado | Mensagem genérica e link (modo dev) | |
| CT36 | Pedir link com e-mail inexistente | Mesma mensagem, sem link | |
| CT37 | Usar o link e definir nova senha | Volta ao login com aviso verde | |
| CT38 | Reutilizar o mesmo link | "Link inválido" | |
| CT39 | Alterar caracteres do token na URL | "Link inválido" | |

## 7. Registro de bugs

| ID | Descrição | Como foi encontrado | Correção | Situação |
|---|---|---|---|---|
| B01 | Link "Voltar" do cadastro dava "Not Found" | Teste manual do cadastro | `href` corrigido para `../index.php` | Corrigido |
| B02 | `declare(strict_types)` fora do topo do `conecta.php` | Primeiro acesso ao cadastro | Movido para a linha 3 | Corrigido |
| B03 | Classe `Relatorio.php` não versionada | Conferência do GitHub | Arquivo adicionado ao Git | Corrigido |