# Manual de uso - Meu Bolso

O Meu Bolso é um sistema de controle financeiro pessoal. Cada pessoa registra suas receitas e despesas e acompanha o saldo. O sistema tem três tipos de acesso: Usuário, Gerente e Administrador.

## 1. Primeiro acesso

Contas de demonstração:

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@meubolso.com | Admin@2026 |
| Gerente | gerente@meubolso.com | Gerente@2026 |
| Usuário | usuario@meubolso.com | Usuario@2026 |

### 1.1 Página inicial
A página inicial apresenta o sistema e leva ao login ou ao cadastro.

![Página inicial](prints/01-inicio.png)

### 1.2 Criar conta
Quem não tem conta clica em Criar conta, informa nome, e-mail e senha (mínimo de 8 caracteres) e confirma. A conta nova é sempre do tipo Usuário.

![Criar conta](prints/02-cadastro.png)

### 1.3 Entrar
Informe e-mail e senha e clique em Entrar. Cada perfil é levado para a sua tela inicial: o Usuário vai ao Dashboard, o Gerente ao Relatório de cadastros e o Administrador à tela Usuários. Para sair, use o botão Sair no canto superior direito.

![Entrar](prints/03-login.png)

## 2. Perfil Usuário

O Usuário é o único perfil que registra movimentação financeira.

### 2.1 Dashboard
Mostra o saldo atual, as receitas e as despesas do mês, a proporção entre despesas e receitas e os últimos lançamentos.

![Dashboard](prints/04-dashboard.png)

### 2.2 Novo lançamento
Clique em Novo lançamento, escolha o tipo (receita ou despesa), a categoria, a data, a descrição e o valor, e salve.

![Novo lançamento](prints/05-novo-lancamento.png)

### 2.3 Histórico
Lista todos os lançamentos. Clique no título de uma coluna para ordenar; clique de novo para inverter a ordem. Quando a categoria tem imagem, a miniatura aparece ao lado do nome.

![Histórico](prints/06-historico.png)

### 2.4 Detalhes
Em Detalhes, veja todos os dados de um lançamento.

![Detalhes](prints/07-detalhes.png)

### 2.5 Editar e excluir
Em Editar, altere os dados e clique em Salvar alterações; para desistir, clique em Cancelar. O botão Excluir remove o lançamento.

![Editar lançamento](prints/08-editar.png)

### 2.6 Gráficos
Mostra as despesas por categoria e o comparativo entre receitas e despesas por mês. É necessário ter acesso à internet.

![Gráficos](prints/09-graficos.png)

### 2.7 Perfil
Permite alterar o nome e a senha. O e-mail não pode ser alterado, porque é o login.

![Perfil](prints/10-perfil.png)

## 3. Perfil Gerente

### 3.1 Relatório de cadastros
Relatório somente de leitura, com o total de cadastros e duas tabelas ordenáveis (usuários e categorias). Por privacidade, o Gerente não vê nenhum valor financeiro.

![Relatório de cadastros](prints/11-relatorio-cadastros.png)

## 4. Perfil Administrador

O Administrador cuida dos cadastros do sistema e também não vê a movimentação financeira dos usuários.

### 4.1 Usuários
Cadastra novos usuários, altera o nível de acesso (Usuário, Gerente ou Administrador) e exclui contas. Uma conta que já tem lançamentos não pode ser excluída, e o sistema avisa.

![Usuários](prints/12-usuarios.png)

### 4.2 Categorias
Cadastra categorias de receita ou despesa, associa uma imagem da biblioteca a cada uma e exclui as que não estão em uso.

![Categorias](prints/13-categorias.png)

### 4.3 Imagens (CRUD)
A tela Imagens gerencia a biblioteca de imagens:

- Cadastrar (upload): escolha o arquivo (JPG, PNG ou WEBP, até 2 MB), dê um título e envie.
- Consultar: a tabela mostra as miniaturas e pode ser ordenada por título, data ou uso.
- Alterar: o botão Editar muda o título e, se desejado, troca o arquivo.
- Excluir: o botão Excluir remove o registro e o arquivo.

![Imagens](prints/14-imagens.png)

![Edição de imagem](prints/15-imagem-edicao.png)

## 5. Segurança

- Cada tela só abre para o perfil autorizado. Quem tenta abrir uma tela de outro perfil vê a mensagem de acesso negado.

![Acesso negado](prints/16-acesso-negado.png)

- As senhas são guardadas com SHA-256 e salt baseado no login.
- Os formulários têm proteção contra CSRF, e o conteúdo exibido é protegido contra XSS.
