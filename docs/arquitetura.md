# Arquitetura - Meu Bolso

## 1. Visão geral

Aplicação web de controle financeiro pessoal em PHP com MySQL, executada no Apache (XAMPP em desenvolvimento). A arquitetura é em camadas simples:

- **Apresentação e controle:** páginas em `SRC/pages/`, que recebem a requisição, validam os dados e montam o HTML.
- **Acesso a dados e regras:** classes em `SRC/classes/`, que concentram as consultas SQL.
- **Persistência:** banco MySQL `meu_bolso`, acessado por PDO (`SRC/conecta.php`).

```
Navegador -> pages/*.php -> classes/*.php -> PDO -> MySQL
```

## 2. Tecnologias

| Tecnologia | Uso |
|---|---|
| PHP 8 (PDO) | Lógica da aplicação e acesso ao banco |
| MySQL/MariaDB | Armazenamento dos dados |
| HTML e CSS | Interface responsiva |
| JavaScript | Filtro de categorias no formulário e gráfico |
| Chart.js (CDN) | Gráfico de gastos por categoria |
| Git e GitHub | Versionamento (branches `main`, `develop` e `feature/...`) |
| Figma | Protótipo das telas |
| XAMPP | Ambiente de desenvolvimento |

## 3. Estrutura de pastas

```
SRC/
  index.php               página inicial
  conecta.php             conexão PDO (não versionado)
  conecta.example.php     modelo da conexão
  classes/
    Usuario.php           cadastro, login, perfil, senha, exclusão de conta
    Categoria.php         listagem de categorias
    Transacao.php         cadastro, listagem, totais, detalhes e exclusão
    Relatorio.php         despesas por categoria (gráfico)
    SenhaReset.php        tokens de recuperação de senha
  pages/
    cadastro, login, logout
    dashboard, nova_transacao, historico, detalhes, graficos
    perfil, configuracoes
    esqueci_senha, redefinir_senha
database/                 script SQL, modelo lógico e documentação do banco
docs/                     requisitos, arquitetura, plano de testes
prototipo/                telas do Figma
```

## 4. Modelo de dados

Tabelas: `usuarios`, `categorias`, `transacoes` e `redefinicoes_senha`.

- Um usuário possui muitas transações (1:N).
- Uma categoria é usada em muitas transações (1:N).
- Um usuário pode ter vários pedidos de redefinição de senha (1:N).

Detalhes de colunas e tipos em `database/documentacao-banco-de-dados.md`.

## 5. Fluxo principal

```
index -> cadastro -> login -> dashboard
dashboard -> nova_transacao | historico -> detalhes | graficos | perfil | configuracoes
login -> esqueci_senha -> redefinir_senha -> login
qualquer página privada -> logout -> index
```

## 6. Padrões adotados

| Padrão | Requisito |
|---|---|
| Consultas preparadas com PDO, nunca concatenadas | RNF-S02 |
| Toda consulta de transação filtra por `id_usuario` | RN-S02 |
| Senhas com `password_hash` e `password_verify` | RNF-S01 |
| Token CSRF em todo formulário que altera dados | RNF-S07 |
| Saída escapada com `htmlspecialchars` | RNF-S06 |
| Sessão regenerada no login e destruída no logout | RNF-S08 |
| Validação no servidor antes de gravar | RNF-S05 |
| Credenciais fora do repositório (`.gitignore`) | RNF-S10 |
| Erros técnicos no log, mensagens amigáveis na tela | RNF09 |
| Mensagem genérica no login e na recuperação de senha | RN-S01 |
| Token de recuperação guardado só como hash, válido por 1 hora e uso único | RN-S05 |

## 7. Decisões e limitações

- **Sem framework:** PHP puro com classes, por ser mais didático e suficiente para o escopo.
- **Sem API:** o cronograma não a exige; as páginas consultam o banco pelas classes.
- **Categorias compartilhadas:** lista única para todos os usuários, sem tabela de contas.
- **Recuperação de senha em modo desenvolvimento:** o link aparece na tela porque o XAMPP não envia e-mail. Em produção, desligar `$modoDev` e enviar por e-mail.
- **Cookies de sessão:** os atributos `HttpOnly` e `SameSite` ainda podem ser configurados (melhoria futura, RNF-S09).
- **HTTPS:** necessário em produção (RNF-S11).