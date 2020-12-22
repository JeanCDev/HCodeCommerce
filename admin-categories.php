<?php

use \Hcode\PageAdmin;
use \Hcode\Model\Category;
use \Hcode\Model\User;
use \Hcode\Model\Product;

// lista das categorias
$app->get('/admin/categories', function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl('categories',['categories'=>$categories]);

});

// página de cadastro de categorias
$app->get('/admin/categories/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl('categories-create');

});

// cadastra categoria
$app->post('/admin/categories/create', function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;

});

// excluir categoria
$app->get('/admin/categories/:idcategory/delete', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;

});

// página para Editar categoria
$app->get('/admin/categories/:idcategory', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl('categories-update',[
		"category"=>$category->getValues()
	]);

});

// salvar edições na categoria
$app->post('/admin/categories/:idcategory', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;

});

// rota para relacionar categoria e produto
$app->get('/admin/categories/:idcategory/products', function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl('categories-products',[
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);

});

// adicionar o produto à categoria 
$app->get('/admin/categories/:idcategory/products/:idproduct/add', 
	function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/$idcategory/products");
	exit;

});

// remover o produto da categoria
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', 
	function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/$idcategory/products");
	exit;

});

?>