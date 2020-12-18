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

}

?>