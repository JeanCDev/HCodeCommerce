<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

// rota para a Home do admin
$app->get('/admin', function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl('index');

});

// rota para a página de login no painel do admin
$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false,
		]);

	$page->setTpl('login');

});

// efetivamente fazer o login
$app->post('/admin/login', function() {

	User::login($_POST['login'], $_POST['password']);

	header("Location: /admin");
	exit;

});

// sair do painel de admin
$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;

});

// página de esqueci minha senha
$app->get('/admin/forgot', function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false,
		]);

	$page->setTpl('forgot');

});

// redefinir senha 
$app->post('/admin/forgot',function(){

	$user = User::getForgot($_POST['email']);

	header("Location: /admin/forgot/sent");
	exit;

});

// página que mostra que o email foi enviado
$app->get('/admin/forgot/sent',function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false,
		]);

	$page->setTpl('forgot-sent');

});

// página para resetar a senha
$app->get('/admin/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false,
		]);

	$page->setTpl('forgot-reset', [
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	]);

});

// lógica para resetar a senha
$app->post('/admin/forgot/reset', function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$user->setPassword($_POST["password"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false,
		]);

	$page->setTpl('forgot-reset-success');

});

?>
