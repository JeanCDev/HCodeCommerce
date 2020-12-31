<?php

use \Hcode\Model\User;
use \Hcode\PageAdmin;

// rota para alterar a senha dos admins
$app->get('/admin/users/:iduser/password', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl('users-password',[
		"user"=>$user->getValues(),
		'msgError' => User::getError(),
		'msgSuccess' => User::getSuccess(),
	]);

});

// altera a senha dos admins
$app->post('/admin/users/:iduser/password', function($iduser){

	User::verifyLogin();

	if(!isset($_POST['despassword']) || $_POST['despassword'] === ''){

		User::setError('Preencha sua nova senha!');
		header("Location: /admin/users/$iduser/password");
		exit();

	}

	if(!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === ''){

		User::setError('Preencha a confirmação da nova senha!');
		header("Location: /admin/users/$iduser/password");
		exit();

	}

	if($_POST['despassword'] !== $_POST['despassword-confirm']){

		User::setError('As senhas devem ser iguais');
		header("Location: /admin/users/$iduser/password");
		exit();

	}

	$user = new User();

	$user->get((int)$iduser);

	$user->setPassword($_POST['despassword']);

	User::setSuccess('Senha alterada com sucesso');
	header("Location: /admin/users/$iduser/password");
	exit();

});

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

	$search = (isset($_GET['search'])) ? $_GET['search'] : '';
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	
	if($search !== ''){

		$pagination = User::getPageSearch($search, $page);

	} else {

		$pagination = User::getPage($page);

	}

	$pages = [];

	for($i = 0; $i < $pagination['pages']; $i++) {

		array_push($pages, [
			'href' => '/admin/users?' . http_build_query([
				"page" => $i+1,
				"search" => $search
			]),
			'text' => $i+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl('users', array(
		"users" => $pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header('Location: /admin/users');
	exit;

});

?>