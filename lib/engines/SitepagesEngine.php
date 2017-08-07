<?php
class SitepagesEngine {
   var $name; var $parms;
   function SitepagesEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      if (Site::GetParms('scriptName')) {
         $isUrl = Sitepages::GetRow(array('url' => Site::GetParms('scriptName')));
         if (!$isUrl['id']) {
            $ins[0]['url'] = Site::GetParms('scriptName');
            if (!Sitepages::Save($ins)) return false;
         }
      }
      return true;
   }
}
?>