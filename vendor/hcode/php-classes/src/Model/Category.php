<?php

namespace Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Mailer;

class Category extends Model{

  // lista todos os usuários
  public static function listAll(){

    $sql = new Sql();

    return $sql->select("SELECT * FROM tb_categories a 
      ORDER BY descategory");

  }

  // salvar nova categoria no banco de dados
  public function save(){

    $sql = new Sql();

    $result = $sql->select("CALL sp_categories_save 
    (:idcategory, :descategory)", 
    [
      ":idcategory" => $this->getidcategory(),
      ":descategory" => $this->getdescategory()
    ]);

    $this->setData($result[0]);

    Category::updateFile();

  }

  // pega informações da categoria
  public function get($idcategory){

    $sql = new Sql();

    $result = $sql->select("SELECT * FROM tb_categories 
        WHERE idcategory = :idcategory", 
      [":idcategory" => $idcategory]);

    $this->setData($result[0]);

  }

  // excluir categoria
  public function delete(){

    $sql = new Sql();

    $sql->query("DELETE FROM tb_categories 
        WHERE idcategory = :idcategory", 
      [":idcategory" => $this->getidcategory()]);

    Category::updateFile();
  }

  // atualiza lista de categoria se alguma for criada ou excluída
  public static function updateFile(){

    $categories = Category::listAll();

    $html = [];

    foreach($categories as $row){

      array_push($html, '<li><a href="/categories/'
        . $row['idcategory'] . '">'
        . $row["descategory"] 
        . '</a></li>');

    }

    file_put_contents(
      $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html",
      implode("",$html)
    );

  }

  // pega as categorias com paginação
  public static function getPage($page = 1, $itemsPerPage = 2) {

    $start = ($page - 1)*$itemsPerPage;

    $sql = new Sql();

    $results = $sql->select(
      "SELECT SQL_CALC_FOUND_ROWS * 
      FROM tb_categories
      ORDER BY descategory
      LIMIT $start, $itemsPerPage");

    $resultTotal = $sql->select("
        SELECT FOUND_ROWS() AS nrtotal
      ");

    return [
      "data"=>$results,
      "total"=>$resultTotal[0]['nrtotal'],
      "pages"=>ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
    ];

  }

  // pega as categorias da pesquisa dá página categories
  public static function getPageSearch($search, $page = 1, $itemsPerPage = 5) {

    $start = ($page - 1)*$itemsPerPage;

    $sql = new Sql();

    $results = $sql->select(
      "SELECT SQL_CALC_FOUND_ROWS * 
      FROM tb_categories 
      WHERE descategory LIKE :search
      ORDER BY descategory
      LIMIT $start, $itemsPerPage",[
        ':search' =>"%".$search."%"
      ]);

    $resultTotal = $sql->select("
        SELECT FOUND_ROWS() AS nrtotal
      ");

    return [
      "data"=>$results,
      "total"=>$resultTotal[0]['nrtotal'],
      "pages"=>ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
    ];

  }

  // pega todos as categorias com paginação
  public function getProductsPage($page = 1, $itemsPerPage = 5) {

    $start = ($page - 1)*$itemsPerPage;

    $sql = new Sql();

    $results = $sql->select(
      "SELECT SQL_CALC_FOUND_ROWS * 
      FROM tb_products a 
      INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
      INNER JOIN tb_categories c ON c.idcategory = b.idcategory
      WHERE c.idcategory = :idcategory
      LIMIT $start, $itemsPerPage", [
        ":idcategory"=>$this->getidcategory()
      ]);

    $resultTotal = $sql->select("
        SELECT FOUND_ROWS() AS nrtotal
      ");

    return [
      "data"=>Product::checkList($results),
      "total"=>$resultTotal[0]['nrtotal'],
      "pages"=>ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
    ];

  }

  // recupera todos os produtos da categoria
  public function getProducts($related = true) {

    $sql = new Sql();

    if ($related === true) {

      return $sql->select("
        SELECT * FROM tb_products WHERE idproduct IN(
          SELECT a.idproduct 
            FROM tb_products a 
            INNER JOIN tb_productscategories b
          ON a.idproduct = b.idproduct
            WHERE b.idcategory = :idcategory
        )", [
            ":idcategory"=>$this->getidcategory()
          ]);

      

    } else {

      return $sql->select("
        SELECT * FROM tb_products WHERE idproduct NOT IN(
          SELECT a.idproduct 
            FROM tb_products a 
            INNER JOIN tb_productscategories b
          ON a.idproduct = b.idproduct
            WHERE b.idcategory = :idcategory
        )", [
          ":idcategory"=>$this->getidcategory()
        ]);

    }

  }

  // adiciona um produto à categoria
  public function addProduct(Product $product){

    $sql = new Sql(); 

    $sql->query("
      INSERT INTO tb_productscategories (idcategory, idproduct)
      VALUES (:idcategory, :idproduct)", [
          ":idcategory"=>$this->getidcategory(),
          ":idproduct"=>$product->getidproduct()
        ]);
    
  }

  // remove um produto da categoria
  public function removeProduct(Product $product){

    $sql = new Sql(); 

    $sql->query("
      DELETE FROM tb_productscategories WHERE idcategory = :idcategory 
       AND idproduct = :idproduct", [
          ":idcategory"=>$this->getidcategory(),
          ":idproduct"=>$product->getidproduct()
        ]);
    
  }

}

?>