<?php

namespace Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Mailer;

class Product extends Model{

  // lista todos os produtos
  public static function listAll(){

    $sql = new Sql();

    return $sql->select("SELECT * FROM tb_products a 
      ORDER BY desproduct");

  }

  // Adiciona a informação em uma lista de itens para mostrar as imagens
  public static function checkList($list){

    foreach($list as &$row){

      $product = new Product();
      $product->setData($row);
      $row = $product->getValues();

    }

    return $list;

  }

  // salvar novo produto no banco de dados
  public function save(){

    $sql = new Sql();

    $result = $sql->select("CALL sp_products_save 
    (:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, 
     :vllength, :vlweight, :desurl)", 
    [
      ":idproduct" => $this->getidproduct(),
      ":desproduct" => $this->getdesproduct(),
      ":vlprice" => $this->getvlprice(),
      ":vlwidth" => $this->getvlwidth(),
      ":vlheight" => $this->getvlheight(),
      ":vllength" => $this->getvllength(),
      ":vlweight" => $this->getvlweight(),
      ":desurl" => $this->getdesurl()
    ]);

    $this->setData($result[0]);

  }

  // pega informações do produto
  public function get($idproduct){

    $sql = new Sql();

    $result = $sql->select("SELECT * FROM tb_products 
        WHERE idproduct = :idproduct", 
      [":idproduct" => $idproduct]);

    $this->setData($result[0]);

  }

  // excluir produto
  public function delete(){

    $sql = new Sql();

    $sql->query("DELETE FROM tb_products 
        WHERE idproduct = :idproduct", 
      [":idproduct" => $this->getidproduct()]);

  }

  // sobrescreve o getValues para adicionar a função de verificar foto
  public function getValues(){

    $this->checkPhotos();

    $values = parent::getValues();

    return $values;

  }

  // verifica se o produto possui foto
  public function checkPhotos(){

    if(file_exists(
      $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . "resources"
      . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "img"
      . DIRECTORY_SEPARATOR . "products" . DIRECTORY_SEPARATOR . 
      $this->getidproduct() . ".jpg")){

        $url = "/resources/site/img/products/".$this->getidproduct().".jpg";

      } else {

        $url = "/resources/site/img/products/product.jpg";

      }

      $this->setdesphoto($url);

  }

  // salva foto com o id do produto
  public function addPhoto($file){

    $extension = explode('.', $file['name']);
    $extension = end($extension);
    
    switch ($extension){

      case 'jpg':
      case 'jpeg':
        $image = imagecreatefromjpeg($file['tmp_name']);
        break;

      case 'png':
        $image = imagecreatefrompng($file['tmp_name']);
        break;

      case 'gif':
        $image = imagecreatefromgif($file['tmp_name']);
        break;
      
    }

    $dist = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . 
            "resources" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR .
            "img" . DIRECTORY_SEPARATOR . "products"  . DIRECTORY_SEPARATOR . 
            $this->getidproduct() . ".jpg";

    imagejpeg($image, $dist);

    imagedestroy($image);

    $this->checkPhotos();

  }

  // mostra as informações do produto pela sua url do banco de dados
  public function getFromURL($desurl){

    $sql = new Sql();

    $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl",
      [
        ":desurl" =>$desurl,
      ]
    );

    $this->setData($rows[0]);

  }

  // pega a categoria do produto
  public function getCategories(){

    $sql = new Sql();

    return $sql->select("
        SELECT * FROM tb_categories a
        INNER JOIN tb_productscategories b
        ON a.idcategory = b.idcategory
        WHERE b.idproduct = :idproduct
      ", [
        ":idproduct" => $this->getidproduct()
      ]);

  }

}

?>