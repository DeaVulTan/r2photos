<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('tablesPath')."Photoarchiv.php");
include_once(Site::GetParms('tablesPath')."Photoimages.php");
class PhotoEngine {
   var $name; var $parms;
   function PhotoEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'photo');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Галерея'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      return $this->viewPhotoarchiv();
   }

   function viewPhotoarchiv() {
      $parms = array();
      if (defined("LANG")) $parms['lang'] = LANG;
      if ($this->parms['part_id']) {
         define("MENU_CRUMBS_LASTLINK", true);
         $parms['id'] = $this->parms['part_id'];
         if (!$part =& Photoarchiv::GetRow($parms)) return false;
         if ($this->parms['href'] <> $part['href']) return false;
         Site::SetParms('bread-crumbs', array($part['name']));
         $items =& Photoimages::GetRows(array('parts_id' => $this->parms['part_id']));
         include(Site::GetTemplate($this->name, 'list'));
      }
      else {
         define("MENU_CRUMBS_LASTLINK", false);
         $parts =& Photoarchiv::GetRows($parms);
         include(Site::GetTemplate($this->name, 'main'));
      }
      return true;
   }
}
?>