<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

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
		"error" => Cart::getMsgError(),
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
	if(!$address->getdesnumber()) $address->setdesnumber('');
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

	$_POST['deszipcode'] = str_replace('-', '', $_POST['zipcode']);
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	$cart = Cart::getFromSession();
	
	$cart->getCalculateTotal();

	$order = new Order();

	$order->setData([
		"idcart"=>$cart->getidcart(),
		"idaddress"=>$address->getidaddress(),
		"iduser"=>$user->getiduser(),
		"idstatus"=>OrderStatus::EM_ABERTO,
		"pvltotal"=>$cart->getvltotal(),
	]);

	$order->save();
	//$cart->removeFromSession();

	
	switch((int)$_POST['payment-method']){
		
		case 1:
			header("Location: /order/".$order->getidorder()); // padrão
			break;
		
		case 2:
			header("Location: /order/".$order->getidorder().'/pagseguro'); // pagseguro
			break;
		
			case 3:
				header("Location: /order/".$order->getidorder().'/paypal'); // paypal
				break;

	}

	exit();

});

// rota para integrar com pagseguro
$app->get('/order/:idorder/pagseguro', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new Page([
		'header' => false,
		'footer' => false,
	]);

	$page->setTpl('payment-pagseguro', [
		"order"=>$order->getValues(),
		"cart"=> $cart->getValues(),
		"products"=>$cart->getProducts(),
		"phone"=>[
			"areaCode"=>substr($order->getnrphone(), 0, 2),
			"number" => substr($order->getnrphone(), 2, strlen($order->getnrphone()))
		]
	]);

});

// rota para integrar com pagseguro
$app->get('/order/:idorder/paypal', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = $order->getCart();

	$page = new Page([
		'header' => false,
		'footer' => false,
	]);

	$page->setTpl('payment-paypal', [
		"order"=>$order->getValues(),
		"cart"=> $cart->getValues(),
		"products"=>$cart->getProducts()
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

	$user->setPassword($_POST['password']);

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

// acessa a página do boleto da compra
$app->get('/order/:idorder', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get($idorder);

	$page = new Page();

	$page->setTpl('payment',[
		"order"=>$order->getValues(),
		"idorder"=>$order->getidorder(),
	]);

});

// cria o boleto da compra
$app->get('/boleto/:idorder', function ($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 10;
$taxa_boleto = 5.00;
$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$valor_cobrado = formatPrice($order->getpvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $order->getdesperson();
$dadosboleto["endereco1"] = $order->getdesaddress() . " " .$order->getdesdistrict();
$dadosboleto["endereco2"] = $order->getdescity()." - ".$order->getdesstate()." - ".$order->getdeszipcode();

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
$dadosboleto["demonstrativo3"] = "";
$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "";		
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - ITAÚ
$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

// DADOS PERSONALIZADOS - ITAÚ
$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

// SEUS DADOS
$dadosboleto["identificacao"] = "Hcode Treinamentos";
$dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
$dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
$dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

// NÃO ALTERAR!
$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'resources'
	. DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include"
	.DIRECTORY_SEPARATOR;
require_once($path . 'funcoes_itau.php');
require_once($path . 'layout_itau.php');

});

// mostra todas os pedidos do cliente
$app->get('/profile/orders', function(){

	User::verifyLogin(false);

	$user = User::getFromSessionId();

	$page = new Page();

	$page->setTpl('profile-orders', [
		"orders"=>$user->getOrders(),
	]);

});

// mostra detalhes de um pedido
$app->get('/profile/orders/:idorder', function($idorder){

	User::verifyLogin(false);

	$order = new Order();

	$order->get((int)$idorder);

	$cart = Cart::getFromSession();

	$cart->getCalculateTotal();

	$page = new Page();

	$page->setTpl('profile-orders-detail', [
		"order"=>$order->getValues(),
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
	]);

});

// rota para trocar a senha do usuário
$app->get('/profile/change-password', function(){

	User::verifyLogin(false);

	$user = User::getFromSessionId();

	$page = new Page();

	$page->setTpl('profile-change-password',[
		"changePassError"=>User::getError(),
		"changePassSuccess"=>User::getSuccess(),
	]);

});

// troca a senha do usuário
$app->post('/profile/change-password', function(){

	User::verifyLogin(false);

	if(!isset($_POST['current_pass']) || $_POST['current_pass'] === ''){

		User::setError('Digite a senha atual');
		header("Location: /profile/change-password");
		exit();

	}

	if(!isset($_POST['new_pass']) || $_POST['new_pass'] === ''){

		User::setError('Digite a nova senha');
		header("Location: /profile/change-password");
		exit();

	}

	if(!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === ''){

		User::setError('Confirme a nova senha');
		header("Location: /profile/change-password");
		exit();

	}

	if($_POST['current_pass'] === $_POST['new_pass']){

		User::setError('Digite uma senha diferente da atual');
		header("Location: /profile/change-password");
		exit();

	}

	$user = User::getFromSessionId();

	if(!password_verify($_POST['current_pass'], $user->getdespassword())){

		User::setError('A senha está invalida');
		header("Location: /profile/change-password");
		exit();

	}

	$user->setdespassword($_POST['new_pass']);

	$user->update();

	User::setSuccess('Senha alterada com sucesso');
	header("Location: /profile/change-password");
	exit();

});

?>