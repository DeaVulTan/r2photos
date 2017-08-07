<?php
class PageEngine {
   var $name; var $parms;
   function PageEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }
   function run() {
      $pageFile = Site::GetParms('absoluteOffsetPath').'pages/'.$this->parms['page'];
      if (file_exists($pageFile)) { include($pageFile); return true; }
      else return false;
   }
}
?>