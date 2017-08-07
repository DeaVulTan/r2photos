<?php
class LayoutEngine {
   var $name; var $parms;
   function LayoutEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }
   function run() {
       $content = ob_get_contents();
      ob_end_clean();
      include(Site::GetTemplate('layout', $this->parms['path']));
      return true;
   }
}
?>