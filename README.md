# Meu Bolso

Aplicação web de controle financeiro pessoal, desenvolvida em PHP e MySQL. Permite registrar receitas e despesas, acompanhar o saldo e visualizar os gastos por categoria.


## Documentação
- [Levantamento de requisitos](docs/levantamento-requisitos.md)
- [Documentação do banco de dados](database/documentacao-banco-de-dados.md)


## Funcionalidades

- Cadastro de usuário, login e logout
- Dashboard com saldo, receitas e despesas do mês
- Cadastro de receitas e despesas por categoria
- Histórico com filtro por mês e paginação
- Detalhes de cada lançamento
- Exclusão de lançamentos com confirmação
- Gráfico de gastos por categoria

## Tecnologias

PHP 8 (PDO), MySQL/MariaDB, HTML, CSS, JavaScript, Chart.js, Git e GitHub, Figma.

## Segurança

- Senhas com `password_hash` e `password_verify`
- Consultas preparadas (PDO)
- Proteção CSRF nos formulários
- Saída escapada contra XSS
- Cada usuário acessa apenas as próprias transações
- Sessão regenerada no login e destruída no logout

## Como rodar localmente

Pré-requisito: XAMPP (Apache + MySQL) instalado.

1. Clone o repositório dentro de `C:\xampp\htdocs\`:
   ```
   git clone https://github.com/Rebecca-ADS/meu-bolso.git
   ```
2. Abra o XAMPP Control Panel e inicie **Apache** e **MySQL**.
3. No phpMyAdmin (`http://localhost/phpmyadmin`), crie o banco `meu_bolso` com agrupamento `utf8mb4_unicode_ci`.
4. Importe o arquivo `meu-bolso.sql` no banco criado.
5. Insira as categorias padrão (aba SQL):
   ```sql
   INSERT INTO categorias (nome, tipo) VALUES
   ('Salário', 'receita'), ('Freelance', 'receita'),
   ('Investimentos', 'receita'), ('Outras receitas', 'receita'),
   ('Alimentação', 'despesa'), ('Moradia', 'despesa'),
   ('Transporte', 'despesa'), ('Saúde', 'despesa'),
   ('Educação', 'despesa'), ('Lazer', 'despesa'),
   ('Outras despesas', 'despesa');
   ```
6. Copie `SRC/conecta.example.php` para `SRC/conecta.php` e ajuste usuário e senha do banco. O `conecta.php` não é versionado.
7. Acesse `http://localhost/<pasta-do-projeto>/SRC/index.php` e crie uma conta.

## Estrutura

```
SRC/
  index.php            página inicial
  conecta.example.php  modelo da conexão com o banco
  classes/             Usuario, Categoria, Transacao, Relatorio
  pages/               cadastro, login, logout, dashboard,
                       nova_transacao, historico, detalhes, graficos
Docs/                  levantamento de requisitos e documentação
protótipo/             telas do Figma
```

## Documentação

- [Levantamento de requisitos](Docs/levantamento-requisitos.md)
- [Arquitetura](docs/arquitetura.md)
- [Plano de testes](docs/plano-de-testes.md)

## Autora

Rebecca Corrêa
