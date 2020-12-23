<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;

// Rota para a Home
$app->get('/', function() {

	$products = Product::listAll();

	$page = new Page();

	$page->setTpl('index', [
		"products"=>Product::checkList($products)
	]);

});

// rota para pegar uma categoria pelo id
$app->get('/categories/:idcategory',function($idcategory){

	$page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;

	$category = new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for($i = 1; $i <= $pagination['pages']; $i++){

		array_push($pages, [
			"link" => '/categories/'.$category->getidcategory().'?page='.$i,
			'page' => $i
		]);
		
	}

	$page = new Page();

	$page->setTpl('category',[
		"category"=>$category->getValues(),
		"products"=>$pagination['data'],
		"pages"=>$pages
	]);

});

// página de informações do produto
$app->get('/products/:desurl', function ($desurl){

	$product = new Product();

	$product->getFromUrl($desurl);

	$page = new Page();

	$page->setTpl('product-detail',[
		"product"=>$product->getValues(),
		"categories"=>$product->getCategories(),
	]);

});

$app->get('/cart', function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl('cart');

});

?>