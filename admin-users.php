<?php

use \Hcode\Model\User;
use \Hcode\PageAdmin;

// excluir um usuário
$app->get('/admin/users/:iduser/delete', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

// lista de usuários
$app->get('/admin/users', function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl('users', array(
		"users" => $users,
	));

});

// página para criar um novo usuário
$app->get('/admin/users/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl('users-create');

});

// página de edição de um usuário
$app->get('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl('users-update', [
		"user"=>$user->getValues()
	]);

});

// criar um novo usuário no banco de dados
$app->post('/admin/users/create', function(){

	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1 : 0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setData($_POST);

	$user->save();

	header('Location: /admin/users');
	exit;

});

// editar um usuário no banco de dados
$app->post('/admin/users/:iduser', function($iduser){

	User::verifyLogin();
	
	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1 : 0;

	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header('Location: /admin/users');
	exit;

});

?>