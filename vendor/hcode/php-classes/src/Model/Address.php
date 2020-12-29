<?php

namespace Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Model;

class Address extends Model{

  const SESSION_ERROR = "AddressError";

  // conecta com a api de endereços
  public static function getCEP($nrcep){

    $nrcep = str_replace('-', '', $nrcep);

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $data = json_decode(curl_exec($curl), true);

    curl_close($curl);

    return $data;

  }

  // carrega informações de endereço a partir do cep
  public function loadFromCEP($nrcep){

    $data = Address::getCEP($nrcep);

    if(isset($data['logradouro']) && $data['logradouro'] !== ''){

      $this->setdesaddress($data['logradouro']);
      $this->setdescomplement($data['complemento']);
      $this->setdesdistrict($data['bairro']);
      $this->setdescity($data['localidade']);
      $this->setdesstate($data['uf']);
      $this->setdescountry('Brasil');
      $this->setdeszipcode($nrcep);

    }

  }

  // salva o endereço no banco de dados
  public function save(){

    $sql = new Sql();

    $results = $sql->select("CALL sp_addresses_save(
        :idaddress, :idperson, :desaddress, 
        :descomplement, :descity, :desstate, 
        :descountry, :deszipcode, :desdistrict
      )",[
        ":idaddress"=>$this->getidaddress(),
        ":idperson"=>$this->getidperson(),
        ":desaddress"=>$this->getdesaddress(),
        ":descomplement"=>$this->getdescomplement(),
        ":descity"=>$this->getdescity(),
        ":desstate"=>$this->getdesstate(),
        ":descountry"=>$this->getdescountry(),
        ":deszipcode"=>$this->getdeszipcode(),
        ":desdistrict"=>$this->getdesdistrict(),
      ]);

    if(count($results) > 0){

      $this->setData($results[0]);

    }

  }

  // salva o erro em uma sessão
  public static function setMsgError($msg){

    $_SESSION[Address::SESSION_ERROR] = $msg;

  }

  // pega o valor do erro na sessão
  public static function getMsgError(){

    $msg =  (isset($_SESSION[Address::SESSION_ERROR]) ? $_SESSION[Address::SESSION_ERROR] : "");

    Address::clearMsgError();

    return $msg;

  }

  // limpa os erros da sessão
  public static function clearMsgError(){

    $_SESSION[Address::SESSION_ERROR] = NULL;

  }

}

?>