<?php
class Redirect302Engine {
   function Redirect302Engine($name, $parms) { $this->name = $name; $this->parms = $parms; }
   function run() {
      header("Location: ".Site::GetBaseRef().$this->parms['url']);
      return true;
   }
}
?>