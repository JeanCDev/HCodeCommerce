<?php

namespace Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Mailer;

class User extends Model{

  // nome da sessão
  const SESSION = "User";
  const SECRET = "secret-code";
  const SECRET_IV = "secret-code";

  // fazer login
  public static function login($login, $password){

    $sql = new Sql();

    $results = $sql->select('SELECT * FROM tb_users 
      WHERE deslogin = :LOGIN',
    [':LOGIN'=>$login]);

    if(count($results) === 0){
      throw new \Exception("Usuário inexistente ou senha inválida", 1);
    }

    $data = $results[0];

    if(password_verify($password, $data['despassword']) === true){

      $user = new User();

      $user->setData($data);

      $_SESSION[User::SESSION] = $user->getValues();

      return $user;

    } else {
      throw new \Exception("Usuário inexistente ou senha inválida", 1);
    }

  }

  // verifica as informações de login
  public static function verifyLogin($inAdmin = true){

    if(
      !isset($_SESSION[User::SESSION]) 
      ||
      !$_SESSION[User::SESSION]
      ||
      !(int)$_SESSION[User::SESSION]['iduser'] > 0
      ||
      (bool)$_SESSION[User::SESSION]['inadmin'] !== $inAdmin
    ){
      header("Location: /admin/login");
      exit;
    }

  }

  // faz logout
  public static function logout(){

    $_SESSION[User::SESSION] = null;

  }

  // lista todos os usuários
  public static function listAll(){

    $sql = new Sql();

    return $sql->select("SELECT * FROM tb_users a 
      INNER JOIN tb_persons b USING (idperson) ORDER BY b.desperson");

  }

  // salva um novo usuário
  public function save(){

    $sql = new Sql();

    $result = $sql->select("CALL sp_users_save 
    (:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", 
    [
      ":desperson" => $this->getdesperson(),
      ":deslogin" => $this->getdeslogin(),
      ":despassword" => $this->getdespassword(),
      ":desemail" => $this->getdesemail(),
      ":nrphone" => $this->getnrphone(),
      ":inadmin" => $this->getinadmin(),
    ]);

    $this->setData($result[0]);

  }

  // recupera a informações do usuário no banco de dados
  public function get($iduser){

    $sql = new Sql();

    $result = $sql->select("SELECT * FROM tb_users a 
    INNER JOIN tb_persons b 
    USING(idperson) WHERE a.iduser = :iduser",[
      ":iduser" => $iduser
    ]);

    $this->setData($result[0]);

  }

  // Atualiza as informações do usuário 
  public function update(){

    $sql = new Sql();

    $result = $sql->select("CALL sp_usersupdate_save 
      (:iduser, :desperson, :deslogin, 
      :despassword, :desemail, :nrphone, :inadmin);", [
      ":iduser" => $this->getiduser(),
      ":desperson" => $this->getdesperson(),
      ":deslogin" => $this->getdeslogin(),
      ":despassword" => $this->getdespassword(),
      ":desemail" => $this->getdesemail(),
      ":nrphone" => $this->getnrphone(),
      ":inadmin" => $this->getinadmin(),
    ]);

    $this->setData($result[0]);

  }

  // exclui um usuário do banco de dados
  public function delete(){

    $sql = new Sql();

    $sql->query("CALL sp_users_delete(:iduser)", [
      ":iduser" => $this->getiduser()
    ]);

  }

  // recupera a senha caso o usuário esqueça
  public static function getForgot($email) {

    $sql = new Sql();

    $result = $sql->select("
      SELECT * FROM tb_persons a 
      INNER JOIN tb_users b USING (idperson)
      WHERE a.desemail = :email
    ", [
        ":email" => $email
      ]);

    if(count($result) === 0){

      throw new \Exception("Não foi possível recuperar a senha");

    } else {

      $data = $result[0];

      $results = $sql->select("CALL sp_userspasswordsrecoveries_create(
        :iduser, :desip
      )", [
        ":iduser" => $data["iduser"],
        ":desip" => $_SERVER["REMOTE_ADDR"]
      ]);

      if(count($results) === 0){

        throw new \Exception("Não foi possível recuperar a senha");

      } else{

        $dataRecovery = $results[0];

        $code = openssl_encrypt(
          $dataRecovery['idrecovery'], 
          'AES-128-CBC', pack("a16", User::SECRET), 0, 
          pack("a16", User::SECRET_IV));

        $code = base64_encode($code);

        $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

        $mailer = new Mailer(
          $data['desemail'], 
          $data['desperson'],
          'Redefinir senha da Hcode Store',
          "forgot",
          [
            "name"=>$data['desperson'],
            "link"=>$link
          ]
        );

        $mailer->send();

        return $data;

      }

    }

  }

  // valida dados do email de recuperação de senha 
  public static function validForgotDecrypt($code){

    $idrecovery = openssl_decrypt(
      base64_decode($code), 
      'AES-128-CBC', 
      pack("a16", User::SECRET), 0, 
      pack("a16", User::SECRET_IV));

    $sql = new Sql();

    $results = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a
        INNER JOIN tb_users b USING (iduser)
        INNER JOIN tb_persons c USING (idperson)
        WHERE 
          a.idrecovery = :idrecovery
          AND
          a.dtrecovery IS NULL
          AND
          DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >=NOW();
    ", ["idrecovery"=>$idrecovery]);

    if(count($results) === 0){

      throw new \Exception("Não foi possível recuperar a senha", 1);

    } else {

      return $results[0];

    }

  }

  // cria uma entrada na tabela de recuperação de senha
  public static function setForgotUsed($idrecovery){

    $sql = new Sql();

    $sql->query("UPDATE tb_userspasswordsrecoveries 
      SET dtrecovery = NOW()
      WHERE idrecovery = :idrecovery
    ", [":idrecovery"=>$idrecovery]);

  }

  // muda a senha do usuário
  public function setPassword($password){

    $sql = new Sql();

    $sql->query("UPDATE tb_users SET despassword = :password
      WHERE iduser = :iduser
    ",[":password"=>$password, ":iduser"=>$this->getiduser()]);

  }

}

?>