🎮 Gamers Nest

Este repositório contém o back-end do sistema Gamers Nest, uma plataforma de e-commerce focada em jogos. O objetivo desta API é fornecer uma estrutura organizada para o gerenciamento de produtos (jogos), categorias e o fluxo de catálogo da loja.

Tecnologias Utilizadas

Camada Tecnologia
Back-end e Front-end|PHP, Laravel|
Banco de Dados|MySQL (via XAMPP)|
Servidor Local|Apache (via XAMPP)|





Para colocar o projeto em funcionamento, siga os passos abaixo:


1\. Preparando o Ambiente (XAMPP)

Abra o XAMPP Control Panel.

Inicie os módulos Apache e MySQL.

Acesse o phpMyAdmin (geralmente em http://localhost/phpmyadmin) e crie um banco de dados chamado gemers\_nest.



2\. Back-end (API)

Abra o terminal na pasta do projeto.

Instale as dependências: composer install

Copie o .env.example para .env e configure o acesso ao banco:

DB\_CONNECTION=mysql

DB\_HOST=127.0.0.1

DB\_PORT=3306

DB\_DATABASE=laravel

DB\_USERNAME=root

DB\_PASSWORD=


Gere a chave: php artisan key:generate

Execute as migrações: php artisan migrate

Inicie o servidor: php artisan serve



Estrutura do Projeto

Gamers-nest

&#x20;┣ 📂 app

&#x20;┃ ┣ 📂 Http

&#x20;┃ ┃ ┣ 📂 Controllers → Lógica de Roupas, Categorias e produtos

&#x20;┃ ┃ ┗ 📂 Requests    → Validação de dados

&#x20;┃ ┣ 📂 Models        → Modelos de Eloquent

&#x20;┃ ┗ 📂 Providers

&#x20;┣ 📂 database        → Migrations e Seeders

&#x20;┣ 📂 routes          → Definição dos endpoints da API

&#x20;┗ 📜 composer.json





Funcionalidades Desenvolvidas

Gestão de Catálogo: Cadastro e consulta de produtos.

Sistema de Categorias: Organização dos jogos por categorias.

Paginação: Implementação de limiters para listagem de itens.

Integração: API estruturada para consumo pelo front-end.




Sobre o Projeto

a Gamers Nest é um e-commerce voltada para a venda de jogos. Este projeto foi desenvolvido utilizando o PHP e o framework Laravel, focando em ter mais facilidade na compra de jogos e aparelhos de informática.

