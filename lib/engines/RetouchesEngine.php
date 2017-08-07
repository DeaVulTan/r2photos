<?php
include_once( Site::GetParms( 'tablesPath' )."Mainmenu.php" );
class RetouchesEngine {
    var $name; var $parms;
    function RetouchesEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'o-fotostudii/retouches');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Примеры ретуши'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
    }

    function run() {
        if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
        return $this->viewList();
    }

    function viewList() {
        define("MENU_CRUMBS_LASTLINK", false);
        $parms = array('active' => 1);
        if (defined("LANG")) $parms['lang'] = LANG;
        $count = Retouches::GetCountRows($parms);
        if ($count > 0) {
            $itemsList = Retouches::GetRows( $parms );

            /*
            $photosList = Retouchesphotos::GetRows( array( 'active' => 1, 'location_in' => implode( ',', array_keys( $itemsList ) ) ) );
            foreach( $itemsList as $id => $item ) {

                foreach( $photosList as $photoId => $photo ) {
                    if( $photo['location_id'] == $item['id'] ) {
                        $itemsList[ $id ]['_photos'][ $photo['id'] ] = $photo;
                        unset( $photosList[ $photoId ] );
                    }
                }
            }
            unset( $photosList );

            */
        }
        include(Site::GetTemplate($this->name, 'list'));
        return true;
    }
}
?>