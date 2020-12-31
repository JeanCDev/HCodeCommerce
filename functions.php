<?php

  use Hcode\Model\User;
  use Hcode\Model\Cart;

  // formata os preços de float para o padrão moeda
  function formatPrice($price){

    if(!$price > 0) $price = 0;

    return number_format($price, 2, ',', '.');

  }

  // verifica o login do usuário
  function checkLogin($inAdmin = true){

    return User::checkLogin($inAdmin);

  }

  // pega o nome do usuário para usar no template
  function getUserName(){

    $user = User::getFromSessionId();

    return utf8_decode($user->getdesperson());

  }

  // pega a quantidade de produtos no carrinho
  function getCartNrQtd(){

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotal();

    return $totals['nrqtd'];

  }

  // pega o valor total do carrinho
  function getCartVlSubtotal(){

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotal();

    return formatPrice($totals['vlprice']);

  }

  // formata a data
  function formatDate($date){

    return date('d/m/Y', strtotime($date));

  }

?> 