<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('libPath')."ThumbnailMaker.php");
class CatalogEngine {
   var $name; var $parms;
   function CatalogEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $this->allPartsList = Catalog::GetMinimums( array( 'active' => 1 ) );
        $parmsT = array('href' => 'catalog');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", 0);
        define("MENU_NAME", 'Каталог');
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['item_id']) return $this->viewItem();
      else if ($this->parms['action'] == 'fabrics') return $this->viewFabrics();
      else if ($this->parms['idC'] && $this->parms['idF']) return $this->viewCF();
      else return $this->viewCatalog();
   }

   function GetCatalogTree() {
    $partsList = Catalog::GetMinimums( array( 'active' => 1, 'parent_id' => 0 ) );
    if( !empty( $partsList ) ) {
        $subPartsList = Catalog::GetMinimums( array( 'active' => 1, 'parent_in' => implode( ',', array_keys( $partsList ) ) ) );
        foreach( $partsList as $partId => $part ) {
            foreach( $subPartsList as $subId => $sub ) {
                if( $sub['parent_id'] == $part['id'] ) {
                    $partsList[ $partId ]['subparts'][ $sub['id'] ] = $sub;
                    unset( $subPartsList[ $subId ] );
                }
            }
        }
    }
    return $partsList;
   }//GetCatalogTree

   function GetSubpartsIdList( $catalogId ) {
    $result = array();
    foreach( $this->allPartsList as $part ) {
        if( $part['parent_id'] == $catalogId ) {
            $result[ $part['id'] ] = $part['id'];
        }
    }
    return $result;
   }//GetSubpartsIdList

   function viewCatalog() {
     $partsList = $this->GetCatalogTree();
     //$subparts = Catalog::GetRows(array('active' => 1, 'parent_id' => $this->parms['id']));
     $page = ( int ) ( $this->parms['page'] ? $this->parms['page'] : 1 );
     $numItemsOnPage = Utils::GetValue('count_items_on_page');
     $numItemsOnPageTablet = 2;
     $numItemsOnPagePhone = 1;
     $parms = array( 'active' => 1 );
     if( $_REQUEST['catalog_id'] > 0 ) {
        $this->parms['id'] = ( int ) $_REQUEST['catalog_id'];
     }
     if( $this->parms['id'] ) {
        $part = Catalog::GetRow( array( 'id' => $this->parms['id'], 'active' => 1 ) );
        if( $part['id'] ) {
            Utils::AddActiveMenu( '/catalog/'.( $part['id'] ).'-'.( $part['href'] ) );
            $parentId = $part['parent_id'];
            while( $parentId ) {
                $part = Catalog::GetRow( array( 'id' => $parentId, 'active' => 1 ) );
                if( $part['id'] ) {
                    Utils::AddActiveMenu( '/catalog/'.( $part['id'] ).'-'.( $part['href'] ) );
                }
                $parentId = $part['parent_id'];
            }//while parentId
        }
        $_REQUEST['catalog_id'] = ( int ) $this->parms['id'];
        $subpartList = $this->GetSubpartsIdList( $this->parms['id'] );
        if( empty( $subpartList ) ) {
            $parms['catalog_id'] = $this->parms['id'];
        } else {
            $parms['catalog_in'] = implode( ',', array( $this->parms['id'] => $this->parms['id'] ) + $subpartList );
        }
     }
     $priceInfo = Items::GetPriceInfo( $parms );
     $priceLimit = array(
        'min' => ( int ) $priceInfo['price_min'],
        'max' => ( int ) $priceInfo['price_max'],
     );
     $_REQUEST['price_min'] = str_replace( ' ', '', $_REQUEST['price_min'] );
     $_REQUEST['price_max'] = str_replace( ' ', '', $_REQUEST['price_max'] );
     if( $_REQUEST['price_min'] > $priceLimit['min'] && $_REQUEST['price_min'] < $priceLimit['max'] ) {
        $_REQUEST['price_min'] = $parms['price_min'] = ( int ) $_REQUEST['price_min'];
     } else {
         unset( $_REQUEST['price_min'] );
     }
     if( $_REQUEST['price_max'] > $priceLimit['min'] && $_REQUEST['price_max'] < $priceLimit['max'] ) {
        $_REQUEST['price_max'] = $parms['price_max'] = ( int ) $_REQUEST['price_max'];
     } else {
         unset( $_REQUEST['price_max'] );
     }

     $count = Items::GetCountRows( $parms );
     //echo '<pre>'; print_r( $parms ); echo '</pre>';
     if ($count > 0) {
        $numPages = ceil( $count / $numItemsOnPage );
        $numPagesTablet = ceil( $count / $numItemsOnPageTablet );
        $numPagesPhone = ceil( $count / $numItemsOnPagePhone );
        $itemsList = Items::GetRows( $parms, array( 'limit' => $numItemsOnPage, 'offset' => ( ( $page - 1 ) * $numItemsOnPage ) ) );
        $itemsListTablet = Items::GetRows( $parms, array( 'limit' => $numItemsOnPageTablet, 'offset' => ( ( $page - 1 ) * $numItemsOnPageTablet ) ) );
        $itemsListPhone = Items::GetRows( $parms, array( 'limit' => $numItemsOnPagePhone, 'offset' => ( ( $page - 1 ) * $numItemsOnPagePhone ) ) );
     }

     //text
     $text = '';
     if( $page == 1 ) {
        $text = $this->menu['content'];
        if( $_REQUEST['catalog_id'] ) {
            $part = Catalog::GetRow( array( 'active' => 1, 'id' => $_REQUEST['catalog_id'] ) );
            $text = $part['description'];
        }
     }

     //Для хлебных крошек
     //define("MENU_CRUMBS_LASTLINK", true);
     //define("CATALOG_ID", $part['id']);
     //define("CATALOG_CRUMBS_ID", $part['parent_id']);
     //Site::SetParms('bread-crumbs', array($part['name']));
     //----------------//
     ob_start();
     include(Site::GetTemplate($this->name, 'list'));

     if( Site::GetParms( 'isAjax' ) ) {
        Utils::JSONResponse( array(
            'url' => $this->MakeUrl( $_REQUEST ),
            'content' => ob_get_clean(),
        ) );
     }
     ob_end_flush();

     return true;
   }

   function viewItem() {
      if (!$item = Items::GetRow(array('id' => $this->parms['item_id'], 'active' => 1))) return false;
      if ($this->parms['href'] <> $item['href']) return false;
      if (!$part = Catalog::GetRow(array('id' => $item['catalog_id'], 'active' => 1))) return false;
      $partsList = $this->GetCatalogTree();

      $catPathIds = array($part['id'], $part['parent_id']);

      // die(var_dump($catPathIds));

      //Для хлебных крошек
      define("MENU_CRUMBS_LASTLINK", true);
      define("CATALOG_ID", $part['id']);
      define("CATALOG_ITEM_ID", $item['id']);
      define("CATALOG_CRUMBS_ID", $part['parent_id']);
      $breadCrumbs = array();
      $breadCrumbs[] = '<a href="'.$this->menuPath.'catalog/'.$part['id'].'-'.$part['href'].'" title="'.htmlspecialchars($part['name']).'">'.$part['name'].'</a>';
      $breadCrumbs[] = $item['name'];
      Site::SetParms('bread-crumbs', $breadCrumbs);
      //----------------//
      include(Site::GetTemplate($this->name, 'one'));
      return true;
   }

   function viewFabrics() {
      define("MENU_CRUMBS_LASTLINK", true);
      if ($this->parms['idF']) {
         if (!$part = Fabrics::GetRow(array('id' => $this->parms['idF']))) return false;
         $page = ($this->parms['page'] ? $this->parms['page'] : 1);
         $numItemsOnPage = Utils::GetValue('count_items_on_page');
         $parms = array('active' => 1, 'fabrics_id' => $this->parms['idF']);
         $count = Items::GetCountRows($parms);
         if ($count > 0) {
            $subparts = Items::GetCatalogs($parms);
            $numPages = ceil($count / $numItemsOnPage);
            $items = Items::GetRows($parms, array('limit' => $numItemsOnPage, 'offset' => (($page - 1) * $numItemsOnPage)));
         }
         //Для хлебных крошек
         $breadCrumbs = array();
         $breadCrumbs[] = '<a href="'.$this->menuPath.'fabrics" title="Производители">Производители</a>';
         $breadCrumbs[] = $part['name'];
         Site::SetParms('bread-crumbs', $breadCrumbs);
         //----------------//
         include(Site::GetTemplate($this->name, 'list-fabrics'));
         return true;
      }
      else {
         Site::SetParms('bread-crumbs', array('Производители'));
         $items = Fabrics::GetRows();
         include(Site::GetTemplate($this->name, 'main-fabrics'));
         return true;
      }
   }

   function viewCF() {
      if (!$part = Catalog::GetRow(array('id' => $this->parms['idC'], 'active' => 1))) return false;
      if (!$fabric = Fabrics::GetRow(array('id' => $this->parms['idF']))) return false;
      $page = ($this->parms['page'] ? $this->parms['page'] : 1);
      $numItemsOnPage = Utils::GetValue('count_items_on_page');
      $parms = array('active' => 1, 'catalog_id' => $this->parms['idC'], 'fabrics_id' => $this->parms['idF']);
      $count = Items::GetCountRows($parms);
      if ($count > 0) {
         $subparts = Items::GetCatalogs($parms);
         $numPages = ceil($count / $numItemsOnPage);
         $items = Items::GetRows($parms, array('limit' => $numItemsOnPage, 'offset' => (($page - 1) * $numItemsOnPage)));
      }
      $urlSmall = ($this->parms['action'] == 'cf' ? 'catalog-fabrics' : 'fabrics-catalog');
      $url = ($this->parms['action'] == 'cf' ? 'catalog-fabrics/'.$this->parms['idC'].'/'.$this->parms['idF'] : 'fabrics-catalog/'.$this->parms['idF'].'/'.$this->parms['idC']);
      $h1 = ($this->parms['action'] == 'cf' ? $part['name'].' &raquo; '.$fabric['name'] : $fabric['name'].' &raquo; '.$part['name']);
      //Для хлебных крошек
      define("MENU_CRUMBS_LASTLINK", true);
      define("CATALOG_ID", $part['id']);
      define("CATALOG_CRUMBS_ID", $part['parent_id']);
      $breadCrumbs = array();
      $breadCrumbs[] = '<a href="'.$this->menuPath.'catalog/'.$part['id'].'-'.$part['href'].'" title="'.htmlspecialchars($part['name']).'">'.$part['name'].'</a>';
      $breadCrumbs[] = $fabric['name'];
      Site::SetParms('bread-crumbs', $breadCrumbs);
      //----------------//
      include(Site::GetTemplate($this->name, 'list-cf'));
      return true;
   }

   function MakeUrl( $request = array() ) {
    if( !isset( $request['url'] ) ) {
        $request['url'] = $this->name;
    }
    $url = $request['url'];
    unset( $request['url'] );
    if( $request['catalog_id'] > 0 ) {
        $url .= '/'.( ( int ) $request['catalog_id'] ).'-'.( $this->allPartsList[ $request['catalog_id'] ]['href'] );
        unset( $request['catalog_id'] );
    } else {
        unset( $request['catalog_id'] );
    }
    if( is_numeric( $request['page'] ) || $request['page'] === '%' ) {
        $url .= '/page/'.( $request['page'] === '%' ? '%' : ( int ) $request['page'] );
        unset( $request['page'] );
    }

    $priceInfo = Items::GetPriceInfo( array( 'active' => 1 ) );
    //print_R($priceInfo);print_R($request);
    if( $request['price_min'] > $priceInfo['price_min'] && $request['price_min'] <= $priceInfo['price_max'] ) {
        $request['price_min'] = ( int ) $request['price_min'];
    } else {
        unset( $request['price_min'] );
    }
    if( $request['price_max'] >= $priceInfo['price_min'] && $request['price_max'] < $priceInfo['price_max'] ) {
        $request['price_max'] = ( int ) $request['price_max'];
    } else {
        unset( $request['price_max'] );
    }

    if( !empty( $request ) ) {
        $url .= '?'.( http_build_query( $request ) );
    }
    return $url;
   }//MakeUrl
}
?>