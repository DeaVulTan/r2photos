<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class PhotographersEngine {
    var $name; var $parms;
    function PhotographersEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'photographers');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Фотографы'));
        define("MENU_PATH", $this->menuPath);

        $this->catalogEngine = Site::GetEngine( 'catalog' );
        $this->catalogList = $this->catalogEngine->GetCatalogTree();

        unset($menu);
    }

    function run() {
        if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
        if ($this->parms['id']) return $this->viewOne();
        return $this->viewList();
    }

    function viewList() {
        define("MENU_CRUMBS_LASTLINK", false);
        $pagerHref = $this->menuPath.'photographers/page/%';
        $page = ($this->parms['page'] ? $this->parms['page'] : 1);
        $numItemsOnPage = ( int ) Utils::GetValue( 'count_photographers_on_page' );
        $parms = array('active' => 1);
        if (defined("LANG")) $parms['lang'] = LANG;
        $photgraphersIds = false;
        $isEmpty = false;
        $itemsParms = array( 'active' => 1 );
        if( $this->parms['catalog_id'] ) {
            $_REQUEST['catalog_id'] = $this->parms['catalog_id'];
            //выбираем id всех фотосессий в выбранном каталоге, затем id привязанных к ним фотографов
            $catalogIdList = array( $this->parms['catalog_id'] );
            $parentId = $this->parms['catalog_id'];
            do {
                $oldCount = count( $catalogIdList );
                $catalogIdList = array_unique( array_merge( $catalogIdList, array_keys( Catalog::GetIds( array( 'active' => 1, 'parent_id' => $parentId ) ) ) ) );
            } while( $oldCount != count( $catalogIdList ) );
            if( count( $catalogIdList ) == 1 ) {
                $itemsParms['catalog_id'] = reset( $catalogIdList );
            } else {
                $itemsParms['catalog_in'] = implode( ',', $catalogIdList );
            }
        }

        $priceInfo = Items::GetPriceInfo( array( 'active' => 1 ) );
        $priceLimit = array(
            'min' => ( int ) $priceInfo['price_min'],
            'max' => ( int ) $priceInfo['price_max'],
        );
        if( $_REQUEST['price_min'] > $priceLimit['min'] && $_REQUEST['price_min'] < $priceLimit['max'] ) {
            $_REQUEST['price_min'] = $itemsParms['price_min'] = ( int ) $_REQUEST['price_min'];
        } else {
             unset( $_REQUEST['price_min'] );
        }
        if( $_REQUEST['price_max'] > $priceLimit['min'] && $_REQUEST['price_max'] < $priceLimit['max'] ) {
            $_REQUEST['price_max'] = $itemsParms['price_max'] = ( int ) $_REQUEST['price_max'];
        } else {
             unset( $_REQUEST['price_max'] );
        }

        //print_r($itemsParms);
        $catalogItemsList = Items::GetMinimums( $itemsParms );
        //echo '<pre>'; print_r( $catalogItemsList ); echo '</pre>';
        $catalogItemsIdsList = array_keys( $catalogItemsList );
        if( !empty( $catalogItemsIdsList ) ) {
            $priceInfo = Items::GetPriceInfo( array( 'active' => 1, 'id_in' => implode( ',', $catalogItemsIdsList ) ) );
        }
        $photgraphersIds = array();
        if( !empty( $catalogItemsIdsList ) ) {
            include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
            $links = new Cataloglinksmgr();
            $photgraphersIds = array_keys( $links->getItemsLinksTo( $links->clType( 'items' ), $catalogItemsIdsList, $links->clType( 'photographers' ) ) );
            unset( $links );
        }
        if( empty( $photgraphersIds ) ) {
            $isEmpty = true;
        } else {
            $parms['id_in'] = implode( ',', $photgraphersIds );
        }

        if( !$isEmpty ) {
            $count = Photographers::GetCountRows( $parms );
            if( $count > 0 ) {
                $numPages = ceil( $count / $numItemsOnPage );
			if ($_SERVER['REQUEST_URI']=='/photographers')unset($parms['id_in']);//если вывод всех фотографов то удаляем фильтр.
                $itemsList = Photographers::GetRows( $parms, array( 'limit' => $numItemsOnPage, 'offset' => ( ( $page - 1 ) * $numItemsOnPage ) ) );
                foreach( $itemsList as $id => $item ) {
                    $itemsList[ $id ]['_photos_count'] = Photoimages::GetCountRows( array( 'parts_id' => $item['id'] ) );
                }
            }
        }

        ob_start();
        include(Site::GetTemplate($this->name, 'list'));

        if( Site::GetParms( 'isAjax' ) ) {
            Utils::JSONResponse( array(
                'url' => $this->catalogEngine->MakeUrl( array( 'url' => $this->name ) + ( array ) $_REQUEST ),
                'content' => ob_get_clean(),
            ) );
        }
        ob_end_flush();

        return true;
    }//viewList

    function viewOne() {
        define("MENU_CRUMBS_LASTLINK", true);
        $parms = array('id' => $this->parms['id'], 'active' => 1);
        if (defined("LANG")) $parms['lang'] = LANG;
        if (!$item = Photographers::GetRow($parms)) return false;
        Site::SetParms('bread-crumbs', array($item['name']));

        include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $links = new Cataloglinksmgr();
        $worksList = $links->getItemsLinksTo( $links->clType( 'photographers' ), $item['id'], $links->clType( 'works' ) );
        unset( $links );

        $page = ( $this->parms['page'] ? $this->parms['page'] : 1 );
        $numItemsOnPage = ( int ) Utils::GetValue( 'count_photographers_photos_on_page' );
        $count = Photoimages::GetCountRows( array( 'parts_id' => $item['id'] ) );
        $photosList = Photoimages::GetRows( array( 'parts_id' => $item['id'] ), array( 'limit' => $numItemsOnPage, 'offset' => ( ( $page - 1 ) * $numItemsOnPage ) ) );
        $numPages = ceil( $count / $numItemsOnPage );

        $priceInfo = Items::GetPriceInfo( array( 'active' => 1 ) );
        $priceLimit = array(
            'min' => ( int ) $priceInfo['price_min'],
            'max' => ( int ) $priceInfo['price_max'],
        );

        include(Site::GetTemplate($this->name, 'one'));
        return true;
    }//viewOne
}
?>