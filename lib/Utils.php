<?php
class Utils {
   static $activeUrlList = array();
   static $cityList = array();
   static function initLangs() {
      include_once(Site::GetParms('tablesPath')."Langs.php");
      $langs = Langs::GetLangs();
      Site::SetParms('langs', $langs);
   }

   static function GetLang($name) {
      $arr = Site::GetParms('langs');
      return $arr[$name]['value_'.LANG];
   }

   static function GetValue($name) {
      include_once(Site::GetParms('tablesPath')."Configs.php");
      return Configs::GetValue($name);
   }

   static function MetaInit() {
      include_once(Site::GetParms('tablesPath')."Meta.php");
      Meta::Init();
      return true;
   }

   static function RandString($length) {
       function make_seed() {
          list($usec, $sec) = explode(' ', microtime());
          return (float) $sec + ((float) $usec * 100000);
       }
       mt_srand(make_seed());
       $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
       $size = strlen($chars);
       for ($i = 0; $i < $length; $i++) $str .= $chars[mt_rand(0, $size - 1)];
       return $str;
   }
	static function GetCertOnMain(){
		$engine=Site::GetEngine('certificates');
		return $engine->onMain();
	}

    static function GetPhotosPortfolioOnMain(){
        $engine=Site::GetEngine('portfolio');
        return $engine->viewListOnMain();
    }



   static function GetMenu( $customParameters = array(), $templateName = 'menu' ) {
      include_once( Site::GetParms('tablesPath')."Mainmenu.php" );
      $parms = $customParameters + array( 'active' => 1, 'parent_id' => 0 );
      if (defined("LANG")) $parms['lang'] = LANG;
      $menus = Mainmenu::GetRows($parms);
      if (sizeOf($menus) > 0) {
        foreach ($menus as $id => $one) {
            $parms['parent_id'] = $id;
            $menus[$id]['submenu'] = array();
            $menus[$id]['submenu'] += ( array ) Mainmenu::GetRows($parms);
            foreach( $menus[$id]['submenu'] as $subId => $subMenu ) {
                $menus[$id]['submenu'][ $subId ]['href'] = $subMenu['path'].$subMenu['href'];
            }
            if( preg_match( '/^catalog\/([1-9][0-9]*)-(.+)$/', $one['href'], $tmp ) ) {
                $catalogId = ( int ) $tmp[ 1 ];
                include_once( Site::GetParms('tablesPath')."Catalog.php" );
                $subCatalogList = Catalog::GetRows( array( 'parent_id' => $catalogId, 'active' => 1 ) );
                foreach( $subCatalogList as $part ) {
                    $menus[$id]['submenu'][] = array(
                        'href' => '/catalog/'.( $part['id'] ).'-'.( $part['href'] ),
                    ) + $part;
                }
            }
        }
      }
      include( Site::GetTemplate( 'layout', $templateName ) );
      return true;
   }

   static function GetTextOnMainPage() {
      include_once(Site::GetParms('tablesPath')."Mainmenu.php");
      $parms = array('href' => 'index');
      if (defined("LANG")) $parms['lang'] = LANG;
      $menu = Mainmenu::GetRow($parms);
      return $menu['content'];
   }

   static function GetSlider() {
      include_once(Site::GetParms('tablesPath')."Slider.php");
      $items = Slider::GetRows();
      include(Site::GetTemplate('layout', 'slider'));
      return true;
   }

   static function GetActions() {
      include_once(Site::GetParms('tablesPath')."Actions.php");
      $itemsList = Actions::GetRows( array( 'active' => 1 ) );//убираем лимит array( 'limit' => Utils::GetValue( 'count_actions_on_main_page' ) )
      if( !empty( $itemsList ) ) {
        while( count( $itemsList ) < 4 ) {
            $itemsList = array_merge( $itemsList, $itemsList );
        }
      }
      include( Site::GetTemplate( 'actions', 'slider' ) );
      return true;
   }//GetActions

   static function GetMostPopularItems() {
      include_once(Site::GetParms('tablesPath')."Items.php");
      $itemsList = Items::GetRows( array( 'active' => 1, 'popular' => 1 ) );
      if( !empty( $itemsList ) ) {
        while( count( $itemsList ) < 4 ) {
            $itemsList = array_merge( $itemsList, $itemsList );
        }
      }
      include(Site::GetTemplate('catalog', 'slider-most-popular'));
      return true;
   }//GetMostPopularItems

   static function GetNewsOnMainPage() {
      $engine = Site::GetEngine('news');
      $ret = $engine->viewOnMainPage();
      unset($engine);
   }

   static function GetVotesOnMainPage() {
      $engine = Site::GetEngine('votes');
      $ret = $engine->viewOnMainPage();
      unset($engine);
   }
   
   static function getCallbackForm()
   {
    $engine = Site::GetEngine('callback');
    $engine->viewFormPopUp();
    unset($engine);
   }

    static function GetMailForm() {
        $engine = Site::GetEngine( 'mail' );
        $engine->viewForm();
        unset( $engine );
    }//GetMailForm

   static function BannersInit($places = array()) {
      $banners = $stats = array();
      include_once(Site::GetParms('tablesPath')."Banners.php");
      if (sizeOf($places) > 0) {
         $_places = Bannersplaces::GetRows(array('id_in' => implode(",", $places)));
         foreach ($places as $idP => $place) {
            $parms = array('active' => 1, 'place_id' => $place, 'check_date' => true, 'check_time' => true, 'check_days' => true, 'check_shows' => true, 'check_clicks' => true);
            if (defined('LANG')) $parms['lang'] = LANG;
            if ($_places[$place]['is_slider']) {
               $bans = Banners::GetRows($parms);
               if (sizeOf($bans) > 0) foreach ($bans as $idB => $ban) {
                  $banners[$place][] = $ban;
                  $stats[] = $ban['id'];
               }
            }
            else {
               $ban = Banners::GetRandom($parms);
               if ($ban['id'] > 0) {
                  $banners[$place][] = $ban;
                  $stats[] = $ban['id'];
               }
            }
         }
      }
      if (sizeOf($stats) > 0 && !preg_match('/(Yandex|Bond|Google|StackRambler|Mail\.Ru|Aport|WebAlta|Yahoo|TurtleScanner|Wget|W3C|Scooter|Omni|Msnbot|MihalismBot|Slurp|gsa|grub|EltaIndexer|curl|Baiduspider|antabot|ia_archiver|Accoona|Teoma|Lycos|Gigabot|GameSpy|Gulper|Pagebull)/i', $_SERVER['HTTP_USER_AGENT']) && $_COOKIE[md5('areYouRobot'.$_SERVER['HTTP_HOST'])]) Banners::Statistic($stats);
      return $banners;
   }

   static function ViewBanner($bans = array()) {
      $ind = 0;
      if (sizeOf($bans) > 1) echo '<div class="banRotate">';
      if (sizeOf($bans) > 0) foreach ($bans as $id => $ban) { include(Site::GetTemplate('layout', 'banner')); $ind ++; }
      if (sizeOf($bans) > 1) echo '</div>';
      return true;
   }

   static function GetCopyright() {
      include_once(Site::GetParms('tablesPath')."Copyright.php");
      $requestUri = preg_replace(array('{^'.Site::GetParms('locationPath').Site::GetParms('offsetPath').'}', '{\?.*$}'), array('', ''), Site::GetParms('requestUri'));
      if (defined("LANG")) $requestUri = preg_replace('/'.LANG.'\//', '', $requestUri);
      $parms = array('active' => 1, 'regexp' => $requestUri);
      if (defined("LANG")) $parms['lang'] = LANG;
      $text = Copyright::GetCopyright($parms);
      //preg_replace(array('/<noindex>/', '/<\/noindex>/'), array('<span class="inv"><![CDATA[<noindex>]]></span>', '<span class="inv"><![CDATA[</noindex>]]></span>'), $text);
      return $text;
   }

   static function GetLinks() {
      include_once(Site::GetParms('tablesPath')."Links.php");
      $requestUri = preg_replace(array('{^'.Site::GetParms('locationPath').Site::GetParms('offsetPath').'}', '{\?.*$}'), array('', ''), Site::GetParms('requestUri'));
      if (defined("LANG")) $requestUri = preg_replace('/'.LANG.'\//', '', $requestUri);
      $parms = array('active' => 1, 'regexp' => $requestUri);
      if (defined("LANG")) $parms['lang'] = LANG;
      $text = Links::GetLinks($parms);
      include(Site::GetTemplate('layout', 'links'));
   }

   static function GetMonth($id, $flag = 1) {
      if (!defined("LANG") || (defined("LANG") && (!LANG || LANG == 'ru'))) {
         $month1 = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
         $month2 = array('', 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек');
         $month3 = array('', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');
      }
      else {
         $month1 = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec');
         $month2 = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec');
         $month3 = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec');
      }
      return ($flag == 1 ? $month1[$id] : ($flag == 2 ? $month2[$id] : $month3[$id]));
   }

   static function GetMonths() {
      if (!defined("LANG") || (defined("LANG") && (!LANG || LANG == 'ru'))) $month = array(1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь');
      else $month = array(1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dec');
      return $month;
   }

   static function LightSearch($text) {
      $text = strip_tags($text);
      $text = preg_replace('/&nbsp;/', ' ', $text);
      $text = preg_replace('/\s+/', ' ', trim($text));
      if (mb_strlen($text, 'utf-8') < 1) return '';
      $pos = mb_strpos(mb_strtolower($text, 'utf-8'), mb_strtolower(strip_tags($_GET['search']), 'utf-8'), 'utf-8');
      if ($pos) $start = (($pos - 125) > 0 ? ($pos - 125) : 0);
      $text = (($pos - 125) > 0 ? '...' : '').mb_substr($text, $start, 255, 'utf-8').(mb_strlen($text, 'utf-8') > 0 ? '...' : '');
      $text = preg_replace('/'.strip_tags($_GET['search']).'/ui', '<span style="background-color: #E8C4FF">'.strip_tags($_GET['search']).'</span>', $text);
      return $text;
   }

   static function GetFastNavigation() {
      $delimeter = '<span>&raquo;</span> ';
      $ret = '<a href="/" title="Главная страница">Главная</a>'.$delimeter;
      
      //Формируем крошки по MENU_ID и MENU_CRUMBS_LASTLINK
      if (defined("MENU_ID") && MENU_ID > 0) {
        if (!defined("MENU_CRUMBS_LASTLINK")) define("MENU_CRUMBS_LASTLINK", false);
        $thisCrumbs = Utils::getTopNav(MENU_ID, '', MENU_CRUMBS_LASTLINK);
      }
      
      //Формируем крошки по CATALOG_CRUMBS_ID
      if (defined("CATALOG_CRUMBS_ID") && CATALOG_CRUMBS_ID > 0) {
        $thisCrumbsCat = Utils::getTopCat(CATALOG_CRUMBS_ID, true);
        if (is_array($thisCrumbsCat)) $thisCrumbs = array_merge($thisCrumbs, $thisCrumbsCat);
      }
      
      //Получаем крошки установленные в Engine
      $currentBreadCrumbs = Site::GetParms('bread-crumbs');
      if (!$currentBreadCrumbs) $currentBreadCrumbs = array();

      if (!$thisCrumbs) $thisCrumbs = array();
      
      //Мержим крошки из сформированные и из Engine
      if (is_array($thisCrumbs)) $currentBreadCrumbs = array_merge($thisCrumbs, $currentBreadCrumbs);
      
      //Выводим, сливая через разделитель
      if (sizeOf($currentBreadCrumbs) > 0) {
        $ret .= implode($delimeter, $currentBreadCrumbs);
      }
      else {
        $ret .= '';
      }
      return $ret;
   }

   function CutFastNavigation( $fastNav ) {
    return ( preg_match( '/^(.+>)(..*?)$/', $fastNav, $tmp ) && strlen( trim( $tmp[ 2 ] ) ) ? trim( $tmp[ 1 ] ) : $fastNav );
   }//CutFastNavigation

   function getTopNav($id, $href, $lastLink = false) {
      $ret = array();
      if (!$id && !$href) return $ret;
      //Поиск меню по ссылке
      if ($href) {
        $tmp = Mainmenu::GetRow(array('href' => $href));
        $ret[] = ($lastLink ? '<a href="'.$tmp['path'].$tmp['href'].'" title="'.htmlspecialchars($tmp['name']).'">' : '').$tmp['name'].($lastLink ? '</a>' : '');
        $lastLink = true;
        $id = $tmp['parent_id'];
      }
      //Поиск меню по ID
      $forId = $id;
      while ($forId) {
         $info = Mainmenu::GetRow(array('id' => $forId));
         $ret[] = ($forId <> $id || $lastLink ? '<a href="'.$info['path'].$info['href'].'" title="'.htmlspecialchars($info['name']).'">' : '').$info['name'].($forId <> $id || $lastLink ? '</a>' : '');
         $forId = $info['parent_id'];
      }
      if (sizeOf($ret)) $ret = array_reverse($ret);
      return $ret;
   }

   function getTopCat($id, $lastLink = false) {
      $ret = array();
      if (!$id) return $ret;
      $forId = $id;
      while ($forId) {
         $info = Catalog::GetRow(array('id' => $forId));
         $ret[] = ($forId <> $id || $lastLink ? '<a href="'.(defined("MENU_PATH") ? MENU_PATH : '/').'catalog/'.$info['id'].'-'.$info['href'].'" title="'.htmlspecialchars($info['name']).'">' : '').$info['name'].($forId <> $id || $lastLink ? '</a>' : '');
         $forId = $info['parent_id'];
      }
      if (sizeOf($ret)) $ret = array_reverse($ret);
      return $ret;
   }
   
   static function GetH1($fastNav) {
    $delimeter = '<span>&raquo;</span> ';
    $piece = explode($delimeter, $fastNav);
    $count = sizeOf($piece) - 1;
    return strip_tags( (trim($piece[$count]) ? trim($piece[$count]) : 'Страница не найдена') );
   }

   static function GetWordPos($count) {
      if (preg_match("/1$/", $count) && $count <> 11) $string = "позиция";
      elseif (preg_match("/[2|3|4]$/", $count) && !preg_match("/1[2|3|4]$/", $count)) $string = "позиции";
      else $string = "позиций";
      return $string;
   }

   static function FormatDate($y, $m, $d) {
      $m = (mb_strlen($m, 'utf-8') < 2 ? '0'.$m : $m);
      $d = (mb_strlen($d, 'utf-8') < 2 ? '0'.$d : $d);
      return $y.$m.$d;
   }

   static function Translit($text) {
      $text = trim(strip_tags($text));
      $source = array('/ё/', '/й/', '/ц/', '/у/', '/к/', '/е/', '/н/', '/г/', '/ш/', '/щ/', '/з/', '/х/', '/ъ/', '/ф/', '/ы/', '/в/', '/а/', '/п/', '/р/', '/о/', '/л/', '/д/', '/ж/', '/э/', '/я/', '/ч/', '/с/', '/м/', '/и/', '/т/', '/ь/', '/б/', '/ю/',
                      '/Ё/', '/Й/', '/Ц/', '/У/', '/К/', '/Е/', '/Н/', '/Г/', '/Ш/', '/Щ/', '/З/', '/Х/', '/Ъ/', '/Ф/', '/Ы/', '/В/', '/А/', '/П/', '/Р/', '/О/', '/Л/', '/Д/', '/Ж/', '/Э/', '/Я/', '/Ч/', '/С/', '/М/', '/И/', '/Т/', '/Ь/', '/Б/', '/Ю/');
      $replace = array('e', 'i', 'tc', 'u', 'k', 'e', 'n', 'g', 'sh', 'shch', 'z', 'kh', '', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'zh', 'e', 'ia', 'ch', 's', 'm', 'i', 't', '', 'b', 'iu',
                       'e', 'i', 'tc', 'u', 'k', 'e', 'n', 'g', 'sh', 'shch', 'z', 'kh', '', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'zh', 'e', 'ia', 'ch', 's', 'm', 'i', 't', '', 'b', 'iu');
      $res = preg_replace($source, $replace, $text);
      $res = preg_replace(array('/[^a-z0-9\-\/]+/i', '/\-+/', '/^\-/', '/\-$/'), array('-', '-', '', ''), $res);
      $piece = explode("-", $res);
      if (sizeOf($piece) > 6) {
        $resArr = array();
        for ($i = 0; $i < 6; $i ++) $resArr[] = $piece[$i];
        $res = implode("-", $resArr);
      }
      return $res;
   }

   static function escapeSphinx($string)
   {
      $from = array ( '\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=' );
      $to   = array ( '\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=' );
      return mysql_real_escape_string(str_replace ( $from, $to, $string ));
   }

    static function GetAddress() {
        $html = trim( Utils::GetValue( 'address_bottom' ) );
       // $html = preg_replace( '/(.+)\(([^0-9]+)\)(.+)\n/', '<strong>\\1(<a href="'.( URL_ADD ).'contacts">\\2</a>)\\3</strong>', $html );
        $html = preg_replace( '/[a-zA-Z0-9._-]+\@[^\s:,]+\.[^\s:,]+/', '<a href="mailto:\\0">\\0</a>', $html );
        return $html;
    }//GetAddress

    //проверка на наличие куки, устанавливаемые при движении мыши (типа капча)
    static function CheckMouseMove() {
        $cookieName = md5( 'r2photos'.date( 'd.m.Y', time() ) );
        return isset( $_COOKIE[ $cookieName ] )
            && $_COOKIE[ $cookieName ] == md5( 'r2photos'.date( 'd.m.Y', time() ).'uhtj405yu04imt3q08yshdlk' )
            ;
    }//CheckMouseMove

    static function JSONResponse( $data, $success = -1 ) {
        header( 'Content-Type: application/json' );
        if( is_bool( $success ) ) {
            $data['success'] = $success;
        }
        die(
            json_encode(
                $data
            )
        );
    }//JSONResponse

    static function GetMenuCategoryMain() {
        include_once( Site::GetParms('tablesPath')."Category.php" );
        $menus = Category::GetRows( array( 'active' => 1, 'in_menu' => 1 ) );
        foreach( $menus as $id => $menu ) {
            $menus[ $id ]['href'] = '/services/'.( $menu['id'] ).'-'.( $menu['href'] );
        }
        include( Site::GetTemplate( 'catalog', 'menu-main-category' ) );
    }//GetMenuCategoryMain

    static function GetPhotographersOnMainPage() {
        include_once(Site::GetParms('tablesPath')."Photographers.php");
        include_once(Site::GetParms('tablesPath')."Photoimages.php");
        $itemsList = Photographers::GetRows( array( 'active' => 1, 'in_slider' => 1 ) );
        if( !empty( $itemsList ) ) {
            foreach( $itemsList as $id => $item ) {
                $itemsList[ $id ]['_photos_count'] = Photoimages::GetCountRows( array( 'parts_id' => $item['id'] ) );
            }
            foreach( $itemsList as $item ) {
                $item['_count'] = 0;
            }
            while( count( $itemsList ) < 4 ) {
                $itemsList = array_merge( $itemsList, $itemsList );
            }
        }
        include( Site::GetTemplate( 'photo', 'slider-photographers' ) );
        return true;
    }//GetPhotographersOnMainPage

    static function GetPartnersOnMainPage() {
        $engine = Site::GetEngine( 'partners' );
        $ret = $engine->viewBottomSlider();
        unset( $engine );
    }//GetPartnersOnMainPage

    static function GetCalendarTitle( $date ) {
        return self::GetMonth( ( int ) date( 'm', $date ), 3 ).' '.date( 'Y', $date );
    }//GetCalendarTitle

    static function GetPageNavigator( $page, $numPages, $url ) {
        $defaultRegexp = '/\/page\/\%/';
        $isPageSl = preg_match($defaultRegexp, $url);
        $ret = ''; 
        if ($page > $numPages) $page = $numPages; 
        $startPage = $page - 4;
        if ($startPage < 2) $startPage = 2;
        $endPage = $page + 4;
        if ($endPage > ($numPages - 1)) $endPage = ($numPages - 1);
        //-----//
        //Первая страница
        $ret .= '<li><a class="'.( $page == 1 ? ' active ' : '' ).'" href="'.($isPageSl ? preg_replace($defaultRegexp, '', $url) : preg_replace('/\%/', 1, $url)).'">1</a></li>'; 
        //-----//
        //Разрыв от 1-й страницы до цикла
        if ($startPage <> 2) $ret .= '<li>…</li>'; 
        //-----//
        for ($i = $startPage; $i <= $endPage; $i++) { 
            if ($i > $numPages) break; 
            $ret .= '<li><a class="'.( $i == $page ? ' active ' : '' ).'" href="'.($isPageSl && ($i == 1) ? preg_replace($defaultRegexp, '', $url) : preg_replace('/\%/', $i, $url)).'">'.$i.'</a></li>'; 
        }
        //Разрыв от цикла до последней страницы
        if ($endPage <> ($numPages - 1)) $ret .= '<li>…</li>'; 
        //-----//
        //Последняя страница
        $ret .= '<li><a class="'.( $page == $numPages ? ' active ' : '' ).'" href="'.preg_replace('/\%/', $numPages, $url).'">'.$numPages.'</a></li>'; 
        //-----//
        return '<ul class="page-papper">'.(defined("IS_ADMIN") && IS_ADMIN ? '<li>'.(defined('LANG') && LANG ? Utils::GetLang('pages') : 'Страницы: ').'</li>' : '').$ret.'</ul>';
    }//GetPageNavigator

    static function GetBasketInfo() {
        $engine = Site::GetEngine( 'orders' );
        $basket = $engine->GetBasketInfo();
        unset( $engine );
        return $basket;
    }//GetBasketInfo

    static function GetPhonesList( $separator = '') {
        $result = explode( "\n", trim( Utils::GetValue( 'top_phones' ) ) );
        foreach( $result as $num => $phone ) {
            $minimal = trim( preg_replace( '/[^0-9]/', '', $phone ) );
            preg_match( '/^\+?([0-9])([0-9]{3})([0-9]{3})([0-9]{2})([0-9]{2})$/', $minimal, $tmp );
            $result[ $num ] = '<a href="tel:+'.( $minimal ).'">+'.( $tmp[ 1 ] ).' ('.( $tmp[ 2 ] ).') '.( $tmp[ 3 ] ).'-'.( $tmp[ 4 ] ).'-'.( $tmp[ 5 ] ).'</a>'.$separator;
        }
        return $result;
    }//GetPhonesList

    const CITY_SESSION_NAME = 'r2photos-city-by-ip';
    static function CheckCityByIP() {
        $city = Site::GetSession( self::CITY_SESSION_NAME );
        if( is_array( $city ) && isset( $city['name'] ) && !empty( $city['name'] ) ) {
            Site::SetParms( 'city-by-ip', $city['name'] );
        } else {
            include_once( Site::GetParms( 'libPath' ).'Geo.php' );
            $parms = array(
                'charset' => 'utf-8',
            );
            $geo = new Geo( $parms );
            $city = array(
                'name'  => $geo->get_value( 'city', false ),
                'ip'    => $geo->get_value( 'ip', false ),
            );
            if( empty( $city['name'] ) ) {
                $city = array(
                    'name' => 'Москва',
                );
            }
            $allowedCityList = self::GetCityList();
            if( !in_array( $city['name'], $allowedCityList ) ) {
                $city['name'] = reset( $allowedCityList );
            }
            Site::SetSession( self::CITY_SESSION_NAME, $city );
            Site::SetParms( 'city-by-ip', $city['name'] );
        }
        return $city['name'];
    }//CheckCityByIP

    static function SetCustomCity( $name ) {
        $city = array(
            'name' => $name,
        );
        Site::SetSession( self::CITY_SESSION_NAME, $city );
        Site::SetParms( 'city-by-ip', $city['name'] );
    }//SetCustomCity

    static function GetOrderInfo( $orderEngine ) {
        $result = array(
            'summ_full' => 0,
            'summ_with_discount' => 0,
            'discount' => 0,
        );
        foreach( $orderEngine->items as $item ) {
            $result['summ_full'] += $item['price'] * $item['count'];
        }

        //
        if( UID > 0 ) {
            $user = Site::GetParms( 'userInfo' );
        }

        $result['summ_with_discount'] = $result['summ_full'];
        return $result;
    }//GetOrderInfo

    static function SelectValue( $valuesList = array() ) {
        foreach( $valuesList as $value ) {
            if( !empty( $value ) ) {
                return $value;
            }
        }
        return reset( $valuesList );
    }//SelectValue

    static function GetCityList() {
        if( empty( self::$cityList ) ) {
            include_once (Site::GetParms ('tablesPath' )."City.php" );
            $tmpList = City::GetRows( array( 'active' => 1 ) );
            foreach( $tmpList as $city ) {
                self::$cityList[] = $city['name'];
            }
        }
        return self::$cityList;
    }//GetCityList

    static function AddActiveMenu( $href ) {
        $href = trim( $href, '/' );
        self::$activeUrlList[ $href ] = $href;
    }//AddActiveMenu

    static function IsActiveMenu( $href ) {
        $href = trim( $href, '/' );
        return isset( self::$activeUrlList[ $href ] );
    }//IsActiveMenu

    static function GetDeliveryList() {
        return array(
            1 => 'Электронный',
            2 => 'Доставка курьером',
            3 => 'Самовывоз',
        );
    }//GetDeliveryList

    static function GetLocationList( $addPrice = false, $certificateId = 0 ) {
        static $result = false;
        if( $result === false ) {
            include_once( Site::GetParms( 'tablesPath' )."Locations.php" );
            $result = array(
                0 => '-',
            );
            if( $certificateId ) {
                include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
                $links = new Cataloglinksmgr();
                $tmpList = $links->getItemsLinksTo( $links->clType( 'certificates' ), $certificateId, $links->clType( 'locations' ) );
                unset( $links );
            } else {
                $tmpList = Locations::GetRows( array( 'active' => 1 ) );
            }
            foreach( $tmpList as $item ) {
                $result[ $item['id'] ] = $item['name'].( $addPrice === true && $item['price'] > 0 ? ' &nbsp; +'.( number_format( $item['price'], 0 ) ).' руб.' : '' );
            }
        }
        return $result;
    }//GetLocationList

    static function GetDefaultCertificateDescription() {
        return '<table width="100%" cellspacing="0" cellpadding="10" border="0">'
            .'<tr>'.( str_repeat( '<th></th>', 5 ) ).'</tr>'
            .str_repeat(
                '<tr>'.str_repeat( '<td></td>', 5 ).'</tr>', 9
            ).'</table>'
        ;
    }//GetDefaultCertificateDescription
}
?>
