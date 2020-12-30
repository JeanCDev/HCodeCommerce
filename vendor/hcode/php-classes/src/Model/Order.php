<?php

  namespace Hcode\Model;
  use \Hcode\Model;
  use \Hcode\Model\Cart;
  use \Hcode\DB\Sql;

  class Order extends Model{

    const ERROR = 'OrderError';
    const SUCCESS = 'OrderSuccess';

    // salva a ordem no banco de dados
    public function save(){

      $sql = new Sql();

      $result = $sql->select("
        CALL sp_orders_save(
          :idorder, :idcart, :iduser,
          :idstatus, :idaddress, :pvltotal
        )
      ", [
        ":idorder" => $this->getidorder(),
        ":idcart" =>$this->getidcart(),
        ":iduser" =>$this->getiduser(),
        ":idstatus" =>$this->getidstatus(),
        ":idaddress" =>$this->getidaddress(),
        ":pvltotal" =>$this->getpvltotal(),
      ]);
      
      if(count($result) > 0){
        $this->setData($result[0]);
      }

    }

    // pega os dados de uma determinada ordem
    public function get($idorder){

      $sql = new Sql();

      $result = $sql->select("
      SELECT * 
			FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus) 
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :idorder
      ", [
        ":idorder"=>$idorder
      ]);

      if(count($result) > 0){
        $this->setData($result[0]);
      }

    }

    // lista todas as ordens
    public static function listAll(){

      $sql = new Sql();

      return $sql->select("
      SELECT * 
			FROM tb_orders a 
        INNER JOIN tb_ordersstatus b USING(idstatus) 
        INNER JOIN tb_carts c USING(idcart)
        INNER JOIN tb_users d ON d.iduser = a.iduser
        INNER JOIN tb_addresses e USING(idaddress)
        INNER JOIN tb_persons f ON f.idperson = d.idperson
      ORDER BY a.dtregister DESC
      ");

    }

    // exclui a ordem do banco de dados
    public function delete(){

      $sql = new Sql();

      $sql->query("DELETE FROM tb_orders WHERE idorder = :idorder",[
        ":idorder"=>$this->getidorder(),
      ]);

    }

    public function getCart():Cart{

      $cart = new Cart();

      $cart->get((int)$this->getidcart());

      return $cart;

    }

    // salva mensagem de sucesso para
  public static function setSuccess($msg){

    $_SESSION[Order::SUCCESS] = $msg;

  }

  // pega mensagens de sucesso 
  public static function getSuccess(){

    $msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : '';

    Order::clearSuccess();

    return $msg;

  }

  // limpa as mensagens de sucesso 
  public static function clearSuccess(){

    $_SESSION[Order::SUCCESS] = NULL;

  }

  // salva os erros na sessão
  public static function setError($msg){

    $_SESSION[Order::ERROR] = $msg;

  }

  // pega os erros da sessão
  public static function getError(){

    $msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[User::ERROR] : '';

    User::clearErrors();

    return $msg;

  }

  // limpa os erros da sessão
  public static function clearErrors(){

    $_SESSION[Order::ERROR] = NULL;

  }

  }

?>