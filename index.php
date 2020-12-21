<?php 

// Classe SQL colocada dentro da pasta vendor
// especificado seu caminho e nome no composer.json
// rodado o comando composer dump-autoload para fazer a importação

session_start();
//session_destroy();
require_once("vendor/autoload.php");

// usa as bibliotecas importadas
use \Slim\Slim;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// configura o Slim framework
$app = new Slim();
$app->config('debug', true);

require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

// roda a aplicação
$app->run();

 ?>