<?php

  namespace Hcode;

  use Rain\TPL;

  class Page{

    private $tpl;
    private $options = [];
    private $defaults = [
      "data"=>[],
      "header"=>true,
      "footer"=>true,
    ];

    // Constrói o HEADER da página
    public function __construct($opts = [], $tpl_dir="/views/"){

      //$this->defaults["data"]["sessions"] = $_SESSION;

      $this->options = array_merge($this->defaults, $opts);

      $config = array(
        "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . $tpl_dir,
        "cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
        "debug"         => false // set to false to improve the speed
         );

      Tpl::configure( $config );

      $this->tpl = new Tpl();

      $this->setData($this->options["data"]);
      
      if($this->options["header"] === true){
        $this->tpl->draw("header", false);
      }
      

    }

    // método para passar os dados para dentro do template
    private function setData($data = []){

      foreach($data as $key => $value){
        $this->tpl->assign($key, $value);
      }

    }

    // constrói o corpo da página
    public function setTpl($name, $data = [], $returnHtml = false){

      $this->setData($data);

      return $this->tpl->draw($name, $returnHtml);

    }

    // Constrói o FOOTER da página
    public function __destruct(){

      if($this->options['footer'] === true){
        $this->tpl->draw("footer");
      }

    }

  }

?>