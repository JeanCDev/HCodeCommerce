<?php

namespace Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Mailer;

class Cart extends Model{

  const SESSION = 'Cart';

  // verifica se a sessão do carrinho já existe
  public static function getFromSession(){

    $cart = new Cart();

    if (isset($_SESSION[Cart::SESSION]) && 
      (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

			  $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

      } else {

        $cart->getFromSessionId();

        if(!(int)$cart->getidcart() > 0){

          $data = [
            'dessessionid'=>session_id()
          ];

          if(User::checkLogin(false)){

            $user = User::getFromSessionId();

            $data['iduser'] = $user->getiduser();

          }

          $cart->setData($data);
          
          $cart->save();
          
          $cart->setToSession();

        }

      }

      return $cart;

  }

  // salva carrinho na sessão
  public function setToSession(){

    $_SESSION[Cart::SESSION] = $this->getValues();

  }

  // paga o carrinho do banco de dados pelo id
  public function get($idcart){

    $sql = new Sql();

    $result = $sql->select("
      SELECT * FROM tb_carts
      WHERE idcart = :idcart
    ", [
      ':idcart' => $idcart]
    );

    if(count($result) > 0){

      $this->setData($result[0]);

    }

  }

  // paga o carrinho do banco de dados pelo session id
  public function getFromSessionId(){

    $sql = new Sql();

    $result = $sql->select("
      SELECT * FROM tb_carts
      WHERE dessessionid = :dessessionid
    ", [
      ':dessessionid' => session_id()
    ]
    );

    if(count($result) > 0){

      $this->setData($result[0]);

    }

  }

  // salva o carrinho no banco de dados
  public function save(){

    $sql = new Sql(); 

    $results = $sql->select("CALL sp_carts_save (
      :idcart, :dessessionid, :iduser, :deszipcode,
      :vlfreight, :nrdays
    )", [
      ':idcart' => $this->getidcart(),
      ':dessessionid' => $this->getdessessionid(),
      ':iduser' => $this->getiduser(),
      ':deszipcode' => $this->getdeszipcode(),
      ':vlfreight' => $this->getvlfreight(),
      ':nrdays' => $this->getnrdays(),
    ]);

    $this->setData($results[0]);

  }

}

?>