<?php

  use Hcode\Model\User;

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

    return $user->getdesperson();

  }



?> 