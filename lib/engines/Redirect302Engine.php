<?php
class Redirect302Engine {   var $name; var $parms;
   function Redirect302Engine($name, $parms) { $this->name = $name; $this->parms = $parms; }
   function run() {      header("http/1.0 302 Moved Temporarily");
      header("Location: ".Site::GetBaseRef().$this->parms['url']);
      return true;
   }
}
?>