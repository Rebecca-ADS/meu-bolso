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

## Documentação

- [Levantamento de requisitos](docs/levantamento-requisitos.md)
- [Arquitetura](docs/arquitetura.md)
- [Plano de testes](docs/plano-de-testes.md)
- [Documentação do banco de dados](database/documentacao-banco-de-dados.md)
  
## Autora

Rebecca Corrêa

## Como rodar com Docker

### Pré-requisitos
- Docker Desktop instalado e aberto (aguarde o "Engine running")
- Git

### Passo a passo

1. Clone o repositório (de preferência fora do OneDrive):

       git clone https://github.com/Rebecca-ADS/meu-bolso.git
       cd meu-bolso

2. Crie o arquivo de conexão a partir do exemplo:

       Copy-Item SRC\conecta.example.php SRC\conecta.php

   Abra `SRC/conecta.php` e use: host `db`, porta `3306`, banco `meu_bolso`, usuário `root`, senha `meu_bolso_dev`.
   O `conecta.php` não vai para o GitHub (está no `.gitignore`).

3. Suba os containers:

       cd prototipo
       docker compose up -d --build

4. Acesse http://localhost:8080

Na primeira execução, o banco importa automaticamente o arquivo `database/meu-bolso.sql`. Aguarde cerca de 1 minuto.

### Comandos úteis

| Ação | Comando |
|------|---------|
| Subir o projeto | `docker compose up -d` |
| Parar o projeto | `docker compose down` |
| Ver logs | `docker compose logs app` / `docker compose logs db` |
| Recriar o banco do zero (apaga os dados) | `docker compose down -v` e depois `docker compose up -d` |

### Acesso externo ao banco
No MySQL Workbench: host `127.0.0.1`, porta `3307`, usuário `root`, senha `meu_bolso_dev`.

### Problemas comuns
- **"Forbidden":** confira se o `compose.yaml` usa `../SRC:/var/www/html`.
- **Erro ao conectar na API do Docker:** o Docker Desktop não está aberto.
- **Cadastro falha:** o banco está sem tabelas. Rode `docker compose down -v` e `docker compose up -d`.
- **Acentos quebrados:** recrie o banco com `down -v`; o compose já força UTF-8.
