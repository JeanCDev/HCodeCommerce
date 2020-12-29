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

	$address = new Address();
	$cart = Cart::getFromSession();

	if(isset($_GET['zipcode'])){

		$_GET['zipcode'] = $cart->getdeszipcode();

	}

	if(isset($_GET['zipcode'])){

		$address->loadFromCEP($_GET['zipcode']);

		$cart->setdeszipcode($_GET['zipcode']);

		$cart->save();

		$cart->getCalculateTotal();

	}	

	if(!$address->getdesaddress()) $address->setdesaddress('');
	if(!$address->getdescomplement()) $address->setdescomplement('');
	if(!$address->getdesdistrict()) $address->setdesdistrict('');
	if(!$address->getdescity()) $address->setdescity('');
	if(!$address->getdesstate()) $address->setdesstate('');
	if(!$address->getdescountry()) $address->setdescountry('');
	if(!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();

	$page->setTpl('checkout', [
		"cart"=>$cart->getValues(),
		"address" => $address->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Address::getMsgError(),
	]);

});

// envia os dados de frete
$app->post('/checkout', function(){

	User::verifyLogin(false);

	if(!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){

		Address::setMsgError("Informe o CEP");
		header("Location: /checkout");
		exit();

	}

	if(!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){

		Address::setMsgError("Informe o Endereço");
		header("Location: /checkout");
		exit();

	}

	if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){

		Address::setMsgError("Informe o Bairro");
		header("Location: /checkout");
		exit();

	}

	if(!isset($_POST['descity']) || $_POST['descity'] === ''){

		Address::setMsgError("Informe a Cidade");
		header("Location: /checkout");
		exit();

	}

	if(!isset($_POST['desstate']) || $_POST['desstate'] === ''){

		Address::setMsgError("Informe o Estado");
		header("Location: /checkout");
		exit();

	}

	if(!isset($_POST['descountry']) || $_POST['descountry'] === ''){

		Address::setMsgError("Informe o País");
		header("Location: /checkout");
		exit();

	}

	$user = User::getFromSessionId();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	header("Location: /order");
	exit();

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

// página de perfil
$app->get('/profile', function(){

	User::verifyLogin(false);

	$user = User::getFromSessionId();

	$page = new Page();

	$page->setTpl('profile', [
		"user"=>$user->getValues(),
		"profileMsg"=>User::getSuccess(),
		"profileError"=>User::getError(),
	]);

});

// editar o perfil
$app->post('/profile', function (){

	User::verifyLogin(false);

	if(!isset($_POST['desperson']) || $_POST['desperson'] === ''){
		User::setError("Preencha seu nome");
		header("Location: /profile");
		exit;
	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] === ''){
		User::setError("Preencha seu email");
		header("Location: /profile");
		exit;
	}

	$user = User::getFromSessionId();

	if($_POST['desemail'] !== $user->getdesemail()){

		if(User::checkLoginExists($_POST['desemail']) === true){

			User::setError("Esse email já está sendo usado por outro usuário");
			header("Location: /profile");
			exit;

		}

	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['deslogin'] = $_POST['desemail'];

	$user->setData($_POST);

	$user->update();

	$_SESSION[User::SESSION] = $user->getValues();

	User::setSuccess('Cadastro atualizados com sucesso');
	
	header("Location: /profile");
	exit;

});

?>