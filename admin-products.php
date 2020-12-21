<?php

use \Hcode\Model\User;
use \Hcode\Model\Product;
use \Hcode\PageAdmin;

// página de listagem de produtos
$app->get('/admin/products', function(){

  User::verifyLogin();

  $products = Product::listAll();

  $page = new PageAdmin();

  $page->setTpl('products', [
    'products' => $products
  ]);

});

// página de criação de produto
$app->get('/admin/products/create', function(){

  User::verifyLogin();

  $page = new PageAdmin();

  $page->setTpl('products-create');

});

// salvar produto n o banco de dados
$app->post('/admin/products/create', function(){

  User::verifyLogin();

  $product = new Product();

  $product->setData($_POST);

  $product->save();

  header('Location: /admin/products');
  exit;

});

// página de edição de produto
$app->get('/admin/products/:idproduct', function($idproduct){

  User::verifyLogin();

  $product = new Product();
  
  $product->get((int)$idproduct);
  
  $page = new PageAdmin();
  
  $page->setTpl('products-update', [
    "product" => $product->getValues()
  ]);

});

// salvar edições no produto
$app->post('/admin/products/:idproduct', function($idproduct){

  User::verifyLogin();

  $product = new Product();

  $product->get((int)$idproduct);

  $product->setData($_POST);

  $product->save();

  $product->addPhoto($_FILES['file']);

  header('Location: /admin/products');
  exit;

});

$app->get('/admin/products/:idproduct/delete', function($idproduct){

  $product = new Product();
  $product->get($idproduct);
  $product->delete();

  header('Location: /admin/products');
  exit;

});

?>