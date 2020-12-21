<?php

use \Hcode\Page;
use \Hcode\Model\Category;

// Rota para a Home
$app->get('/', function() {

	$page = new Page();

	$page->setTpl('index');

});

// rota para pegar uma categoria pelo id
$app->get('/categories/:idcategory',function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl('category',[
		"category"=>$category->getValues(),
		"products"=>[]
	]);

});

?>