<?php
include_once( Site::GetParms( 'tablesPath' )."Mainmenu.php" );
class PortfolioEngine {
    var $name; var $parms;
    function PortfolioEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'o-fotostudii/nashi-fotografii');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Портфолио'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
    }

    function run() {
        //print_r($this->parms);        die('fdg');
        if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
        if ($this->parms['action'] == 'photos-lazy-load') { return $this->viewPhotosLazyLoad(); }
        else if ($this->parms['action'] == 'load-more-photos') { return $this->ajaxLoadMorePhotos(); }
        return $this->viewList();
    }

    function viewList() {
        define("MENU_CRUMBS_LASTLINK", false);
        $parms = array('active' => 1);
        if (defined("LANG")) $parms['lang'] = LANG;
        $count = Portfolio::GetCountRows($parms);
        if ($count > 0) {
            $itemsList = Portfolio::GetRows( $parms, array(), array('id' => 'desc') );

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

        function viewListOnMain() {
        define("MENU_CRUMBS_LASTLINK", false);
        $parms = array('active' => 1);
        if (defined("LANG")) $parms['lang'] = LANG;
        $count = Portfolio::GetCountRows($parms);
        if ($count > 0) {
            $itemsList = Portfolio::GetRows( $parms, array('limit' => 30), array('id' => 'desc') );

        }
        include(Site::GetTemplate($this->name, 'list-on-main'));
        return true;
    }

    function viewPhotosLazyLoad() {

        //Для хлебных крошек
        $breadCrumbs = array();
        $breadCrumbs[] = 'Фото клиентов';
        Site::SetParms('bread-crumbs', $breadCrumbs);

        $parms = array('active' => 1);
        if (defined("LANG")) $parms['lang'] = LANG;
        $count = Portfolio::GetCountRows($parms);
        if ($count > 0) {
            $itemsList = Portfolio::GetRows( $parms, array('limit' => 6), array('id' => 'desc') );

        }

        include(Site::GetTemplate($this->name, 'photos-lazy-load'));
        return true;

    }

    function ajaxLoadMorePhotos()
    {

        $numsPhoto = Portfolio::GetCountRows( array('active' => 1 ));

        $startFrom = $this->parms['start'];
        //echo "nums $numsPhoto , start: $startFrom <br>";

        if ($startFrom < $numsPhoto) {
            $parms = array('active' => 1);
            if (defined("LANG")) $parms['lang'] = LANG;

            $itemsList = Portfolio::GetRows($parms, array('offset' => $startFrom, 'limit' => 6), array('id' => 'desc'));

            if (count($itemsList) < 1) {
                die;
            }

            //echo "count items for foreach: ", count($itemsList),'<br>';
            foreach ($itemsList as $k => $v) { ?>

                <a href="<?php echo $v['picture']; ?>" rel="pictures-list" class="item  cboxElement"
                   data-picture="/<?php echo $v['picture']; ?>" title="">
                    <img src="/<?php echo $v['picture']; ?>">
                    <span><?php echo $v['name']; ?></span>
                </a>

            <?php }

        }

        return true;
    }
}
