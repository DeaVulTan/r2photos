<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class LicensesEngine {
   var $name; var $parms;
   function LicensesEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'licenses');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Лицензии и сертификаты'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      return $this->viewLicenses();
   }

   function viewLicenses()
   {
    $parms = array('active' => 1);
    $count =& Licenses::GetCountRows($parms);
    if ($count > 0) $items =& Licenses::GetRows($parms, array('limit' => $numArticlesOnPage, 'offset' => (($page - 1) * $numArticlesOnPage)));
    include(Site::GetTemplate($this->name, 'list'));
    return true;
   }
}
?>