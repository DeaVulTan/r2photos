<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class VacsEngine {
   var $name; var $parms;
   function VacsEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'vacancies');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Вакансии'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['id']) return $this->viewVac();
      else return $this->viewVacs();
   }

   function viewVacs() {
      define("MENU_CRUMBS_LASTLINK", false);
      $parms = array();
      if (defined("LANG")) $parms['lang'] = LANG;
      $vacs =& Vacs::GetRows($parms);
      include(Site::GetTemplate($this->name, 'list'));
      return true;
   }

   function viewVac() {
      define("MENU_CRUMBS_LASTLINK", true);
      $parms = array('id' => $this->parms['id']);
      if (defined("LANG")) $parms['lang'] = LANG;
      if (!$vac =& Vacs::GetRow($parms)) return false;
      if ($this->parms['href'] <> $vac['href']) return false;
      Site::SetParms('bread-crumbs', array($vac['name']));
      include(Site::GetTemplate($this->name, 'one'));
      return true;
   }
}
?>