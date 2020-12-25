<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;

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

// página do carrinho de compras
$app->get('/cart', function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl('cart', [
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
		"error" => Cart::getMsgError()
	]);

});

// adicionar produto no carrinho
$app->get('/cart/:idproduct/add', function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$qtd = isset($_GET['qtd']) ? (int)$_GET['qtd'] : 1;
	
	$cart = Cart::getFromSession();

	for($i = 0; $i < $qtd; $i++){

		$cart->addProduct($product);

	}

	header("Location: /cart");
	exit;

});

// tirar um produto do carrinho
$app->get('/cart/:idproduct/minus', function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;

});

// remove todos os produtos de um tipo
$app->get('/cart/:idproduct/remove', function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;

});

// rota para pegar informações de frete
$app->get('/cart/freight', function (){

	$cart = Cart::getFromSession();

	$cart->setFreight($_GET['zipcode']);

	header("Location: /cart");
	exit;

});

// rota para prosseguir para o checkout
$app->get('/checkout', function(){

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl('checkout', [
		"cart"=>$cart->getValues(),
		"address" => $address->getValues(),
	]);

});

// página de login do usuário
$app->get('/login', function(){

	$page = new Page();

	$page->setTpl('login', [
		"error"=>User::getError(),
		"errorRegister"=>User::getErrorRegister(),
		"registerValues" =>(isset($_SESSION['registerValues']) 
			? $_SESSION['registerValues'] : [
				"name"=>"",
				"email"=>"",
				"phone"=>""
			])
	]);

});

// fazer o login como usuário comum
$app->post('/login', function(){

	try {

		User::login($_POST['login'], $_POST['password']);

	} catch (Exception $exception) {

		User::setError($exception->getMessage());

	}

	header("Location: /checkout");
	exit();

});

// fazer logout como usuário comum
$app->get('/logout', function(){

	User::logout();

	header("Location: /login");
	exit();

});

// registra um novo usuário comum
$app->post('/register', function(){

	$_SESSION['registerValues'] = $_POST;

	if(!isset($_POST["name"]) || $_POST["name"] == ""){

		User::setErrorRegister("Preencha seu nome completo");
		header("Location: /login");
		exit();

	}

	if(!isset($_POST["email"]) || $_POST["email"] == ""){

		User::setErrorRegister("Preencha seu email");
		header("Location: /login");
		exit();

	}

	if(!isset($_POST["password"]) || $_POST["password"] == ""){

		User::setErrorRegister("Preencha sua senha");
		header("Location: /login");
		exit();

	}

	if (User::checkLoginExists($_POST["email"]) === true) {

		User::setErrorRegister("Este email já está sendo usado por um usuário");
		header("Location: /login");
		exit();

	}

	$user = new User();

	$user->setData([
		"inadmin" => 0,
		"deslogin" => $_POST["email"],
		"desperson" => $_POST["name"],
		"desemail" => $_POST["email"],
		"despassword" => $_POST["password"],
		"nrphone" => $_POST["phone"]
	]);

	$user->save();

	User::login($_POST["email"], $_POST["password"]);

	header("Location: /checkout");
	exit;

});

// página de esqueci minha senha do usuário comum
$app->get('/forgot', function(){

	$page = new Page();

	$page->setTpl('forgot');

});

// redefinir senha do usuário comum
$app->post('/forgot',function(){

	$user = User::getForgot($_POST['email'], false);

	header("Location: /forgot/sent");
	exit;

});

// página que mostra que o email foi enviado do usuário comum
$app->get('/forgot/sent',function(){

	$page = new Page();

	$page->setTpl('forgot-sent');

});

// página para resetar a senha do usuário comum
$app->get('/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new Page();

	$page->setTpl('forgot-reset', [
		"name"=>$user["desperson"],
		"code" => $_GET["code"]
	]);

});

// lógica para resetar a senha do usuário comum
$app->post('/forgot/reset', function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

	$user->setPassword($password);

	$page = new Page();

	$page->setTpl('forgot-reset-success');

});

?>