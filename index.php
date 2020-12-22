<?php 

// Classe SQL colocada dentro da pasta vendor
// especificado seu caminho e nome no composer.json
// rodado o comando composer dump-autoload para fazer a importação

session_start();
//session_destroy();
require_once("vendor/autoload.php");

// usa as bibliotecas importadas
use \Slim\Slim;

// Inicia as variáveis ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// configura o Slim framework
$app = new Slim();
$app->config('debug', true);

// funções úteis
require_once("functions.php");

// rotas do site para clientes
require_once("site.php");

// login e home do admin
require_once("admin.php");

// páginas de usuários do admin
require_once("admin-users.php");

// páginas para editar categorias do admin
require_once("admin-categories.php");

// página para editar Produtos do admin
require_once("admin-products.php");

// roda a aplicação
$app->run();

 ?>