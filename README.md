# Meu Bolso

Sistema web de controle financeiro pessoal, desenvolvido em **PHP procedural** com **MySQL/MariaDB**, como projeto da Curricularização da Extensão (Análise e Desenvolvimento de Sistemas, 4º semestre).

- Repositório: https://github.com/Rebecca-ADS/meu-bolso
- Autoria: Rebecca-ADS (incluir aqui o nome completo de todos os integrantes do grupo)

## Funcionalidades

- Cadastro e login com três níveis de acesso
- Usuário: dashboard, novo lançamento, histórico, gráficos, edição e exclusão de lançamentos
- Gerente: relatório de cadastros (somente leitura, sem valores financeiros)
- Administrador: gerenciamento de usuários, categorias e imagens (CRUD completo)
- Relatórios com tabelas ordenáveis por coluna
- Gerenciamento de imagens: upload, listagem, edição e remoção; as imagens são ligadas às categorias e aparecem no histórico do usuário
- Gráficos de despesas por categoria e de receitas x despesas por mês

## Níveis de acesso

| Nível | Perfil | O que acessa |
|---|---|---|
| 1 | Usuário | Dashboard, Histórico, Gráficos, lançamentos, Perfil |
| 2 | Gerente | Relatório de cadastros, Perfil |
| 3 | Administrador | Usuários, Categorias, Imagens, Perfil |

Princípio adotado: cada perfil enxerga apenas o necessário para sua função (minimização de dados). Somente o usuário comum tem movimentação financeira; gerente e administrador administram cadastros, sem ver valores.

## Tecnologias

PHP 8 procedural (sem orientação a objetos), MySQLi com Prepared Statements, MariaDB/MySQL, HTML, CSS e JavaScript, Google Charts (exige internet), Git e GitHub.

## Segurança

- Senha com SHA-256 e salt baseado no login: sha256(sha256(login) + senha)
- Prepared Statements em todas as consultas ao banco
- Saída escapada contra XSS e token CSRF nos formulários
- Sessão regenerada no login e destruída no logout
- Upload de imagens validado: tamanho máximo de 2 MB, tipo real do arquivo (JPG, PNG ou WEBP), nome aleatório e bloqueio de execução de PHP na pasta uploads
- Controle de acesso por nível em todas as páginas

## Instalação (XAMPP)

Pré-requisitos: XAMPP com Apache e MySQL/MariaDB, PHP 8 ou superior e navegador com acesso à internet (para os gráficos).

1. Copie a pasta do projeto para C:\xampp\htdocs\meu-bolso (o nome da pasta deve ser meu-bolso).
2. Abra o painel do XAMPP e inicie o Apache e o MySQL.
3. Acesse http://localhost/phpmyadmin, clique na aba SQL (sem escolher nenhum banco), cole o conteúdo de database/meu-bolso.sql e clique em Executar. O script cria o banco meu_bolso, as tabelas, as categorias e as contas de primeiro acesso.
4. Na pasta SRC, copie conecta.example.php para conecta.php e ajuste usuário, senha e nome do banco. No XAMPP padrão: usuário root, senha vazia, banco meu_bolso.
5. Acesse http://localhost/meu-bolso/SRC/index.php

A pasta SRC/uploads precisa permitir gravação pelo Apache (no XAMPP para Windows já permite).

## Primeiro acesso

Contas de demonstração, criadas pelo script do banco:

| Perfil | E-mail (login) | Senha |
|---|---|---|
| Administrador | admin@meubolso.com | Admin@2026 |
| Gerente | gerente@meubolso.com | Gerente@2026 |
| Usuário | usuario@meubolso.com | Usuario@2026 |

Recomenda-se trocar as senhas na página Perfil após o primeiro acesso.

## Estrutura do projeto

    meu-bolso/
    ├── SRC/
    │   ├── index.php            página inicial
    │   ├── conecta.example.php  modelo de conexão (copiar para conecta.php)
    │   ├── includes/            funções (auth, usuarios, categorias, imagens, upload, transacoes, ordenacao)
    │   ├── pages/               telas do sistema
    │   ├── assets/              CSS e imagens fixas
    │   └── uploads/             imagens enviadas pelo administrador
    ├── database/
    │   └── meu-bolso.sql        script de criação do banco
    └── docs/                    documentação de apoio

## Observações do projeto

- O projeto foi adaptado ao enunciado: a versão anterior, orientada a objetos, permanece no histórico do Git (branches main e develop).
- Foram removidos a recuperação de senha por e-mail e a tela de configurações, por não fazerem parte dos requisitos.
- O arquivo SRC/conecta.php não é versionado, por conter dados de acesso ao banco.