<?php

  namespace Hcode\Model;
  use \Hcode\Model;
  use \Hcode\DB\Sql;

  class OrderStatus extends Model{

    // códigos de status do pedido
    const EM_ABERTO = 1;
    const AGUARDANDO_PAGAMENTO = 2;
    const PAGO = 3;
    const ENTREGUE = 4;

    // lista todos os status do banco de dados
    public static function listAll(){

      $sql = new Sql();

      return $sql->select("
        SELECT * FROM tb_ordersstatus ORDER BY desstatus
      ");

    } 

  }

?>