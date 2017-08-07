<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class PartnersEngine {
   var $name; var $parms;
   function PartnersEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'partners');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Партнеры'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['id']) return $this->viewPartner();
      else return $this->viewPartners();
   }

   function viewPartners() {
      define("MENU_CRUMBS_LASTLINK", false);
      $parms = array();
      if (defined("LANG")) $parms['lang'] = LANG;
      $count =& Partners::GetCountRows($parms);
      if ($count > 0) $items =& Partners::GetRows($parms);
      include(Site::GetTemplate($this->name, 'list'));
      return true;
   }

   function viewPartner() {
      define("MENU_CRUMBS_LASTLINK", true);
      $parms = array('id' => $this->parms['id']);
      if (defined("LANG")) $parms['lang'] = LANG;
      if (!$item =& Partners::GetRow($parms)) return false;
      if ($this->parms['href'] <> $item['href']) return false;
      Site::SetParms('bread-crumbs', array($item['name']));
      include(Site::GetTemplate($this->name, 'one'));
      return true;
   }

    function viewBottomSlider() {
        $itemsList = Partners::GetRows( array( 'active' => 1 ) );
        if( !empty( $itemsList ) ) {
            while( count( $itemsList ) < 6 ) {
                $itemsList = array_merge( $itemsList, $itemsList );
            }
        }
        include( Site::GetTemplate( $this->name, 'slider' ) );
        return true;
    }//viewBottomSlider
}
?>