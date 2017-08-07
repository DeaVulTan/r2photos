<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class ActionsEngine {
   var $name; var $parms;
   function ActionsEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'actions');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Акции'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['id']) return $this->viewOne();
      else return $this->viewActions();
   }

   function viewActions() {
      define("MENU_CRUMBS_LASTLINK", false);
      $page = ($this->parms['page'] ? $this->parms['page'] : 1);
      $numItemsOnPage = Utils::GetValue('count_actions_on_page');
      $parms = array('active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      $count = Actions::GetCountRows($parms);
      if ($count > 0) {
         $numPages = ceil($count / $numItemsOnPage);
         $itemsList = Actions::GetRows($parms, array('limit' => $numItemsOnPage, 'offset' => (($page - 1) * $numItemsOnPage)));
      }
      include(Site::GetTemplate($this->name, 'list'));
      return true;
   }

   function viewOne() {
      define("MENU_CRUMBS_LASTLINK", true);
      $parms = array('id' => $this->parms['id'], 'active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      if (!$item = Actions::GetRow($parms)) return false;
      if ($this->parms['href'] <> $item['href']) return false;
      Site::SetParms('bread-crumbs', array($item['name']));
      include(Site::GetTemplate($this->name, 'one'));
      return true;
   }
}
?>