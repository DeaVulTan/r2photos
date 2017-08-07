<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class NewsEngine {
   var $name; var $parms;
   function NewsEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'news');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Новости'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['id']) return $this->viewNew();
      else if ($this->parms['action'] == 'archiv') return $this->viewArchiv();
      else if ($this->parms['action'] == 'viewcalendar') return $this->viewOnlyCalendar();
      else return $this->viewNews();
   }

   function viewNews() {
      define("MENU_CRUMBS_LASTLINK", false);
      JS::enable('site/news');
      $pagerHref = $this->menuPath.'news/page/%';
      $page = ($this->parms['page'] ? $this->parms['page'] : 1);
      $numNewsOnPage = ( int ) Utils::GetValue( 'count_news_on_page' );
      $numNewsOnPageTablet = 2;
      $numNewsOnPagePhone = 1;
      $parms = array('active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      $count = News::GetCountRows($parms);
      if ($count > 0) {
         $numPages = ceil($count / $numNewsOnPage);
         $numPagesTablet = ceil($count / $numNewsOnPageTablet);
         $numPagesPhone = ceil($count / $numNewsOnPagePhone);
         $news = News::GetRows($parms, array('limit' => $numNewsOnPage, 'offset' => (($page - 1) * $numNewsOnPage)));
         $newsTablet = News::GetRows($parms, array('limit' => $numNewsOnPageTablet, 'offset' => (($page - 1) * $numNewsOnPageTablet)));
         $newsPhone = News::GetRows($parms, array('limit' => $numNewsOnPagePhone, 'offset' => (($page - 1) * $numNewsOnPagePhone)));
      }
      if ($this->parms['date'] && preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->parms['date'], $m)) {
         $year = $m[1];
         $month = $m[2];
         $parms['dateYmd'] = $this->parms['date'];
         $news = News::GetRows($parms);
      }
      else {
         $maxDate = News::GetMaxDate($parms);
         $year = date("Y", $maxDate);
         $month = date("m", $maxDate);
         $stringNo = 'Выберите дату';
      }
      $date = $year.$month;
      $months = Utils::GetMonths();
      ob_start();
       $this->viewCalendar( $date );
       $calendar = ob_get_contents();
      ob_end_clean();
      include(Site::GetTemplate($this->name, 'list'));
      return true;
   }

   function viewNew() {
      define("MENU_CRUMBS_LASTLINK", true);
      $parms = array('id' => $this->parms['id'], 'active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      if (!$new = News::GetRow($parms)) return false;
      if ($this->parms['href'] <> $new['href']) return false;
      Site::SetParms('bread-crumbs', array('<a href="/news" title="Новости">Новости</a>', $new['name']));
      include(Site::GetTemplate($this->name, 'one'));
      return true;
   }

   function viewOnMainPage() {
      $parms = array('active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      $news = News::GetRows($parms, array('limit' => Utils::GetValue('count_news_on_main_page')));
      include(Site::GetTemplate($this->name, 'main'));
      return true;
   }

   function viewArchiv() {
      define("MENU_CRUMBS_LASTLINK", true);
      JS::enable('site/news');
      $parms = array('active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      $selectedDay = '';
      if ($this->parms['date'] && preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})?/', $this->parms['date'], $m)) {
         $pagerHref = $this->menuPath.'news/archiv/'.( $this->parms['date'] ).'/page/%';
         $year = $m[1];
         $month = $m[2];
         $maxDate = strtotime( '01.'.$month.'.'.$year );
         if( $m[ 3 ] ) {
            $selectedDay = $parms['dateYmd'] = $this->parms['date'];
         } else {
            $parms['dateYm'] = $this->parms['date'];
         }
         $date = $this->parms['date'];
         $page = ($this->parms['page'] ? $this->parms['page'] : 1);
         $numNewsOnPage = ( int ) ( $this->parms['perpage'] > 0 && $this->parms['perpage'] <= 10 ? $this->parms['perpage'] : Utils::GetValue( 'count_news_on_page' ) );
         $count = News::GetCountRows( $parms );
         if( $count ) {
            $numPages = ceil( $count / $numNewsOnPage );
            $news = News::GetRows( $parms, array( 'limit' => $numNewsOnPage, 'offset' => ( ( $page - 1 ) * $numNewsOnPage ) ) );
         }
      }
      else {
         $pagerHref = $this->menuPath.'news/archiv/page/%';
         $maxDate = News::GetMaxDate($parms);
         $year = date("Y", $maxDate);
         $month = date("m", $maxDate);
         $date = $year.$month;
         $stringNo = 'Выберите дату';
      }
      $months = Utils::GetMonths();
      Site::SetParms('bread-crumbs', array($months[($month*1)].' '.$year));
      ob_start();
       $this->viewCalendar($date);
       $calendar = ob_get_contents();
      ob_end_clean();
      if( Site::GetParms( 'isAjax' ) ) {
        Utils::JSONResponse( array( 'content' => $calendar ) );
      }
      include(Site::GetTemplate($this->name, 'list'));
      return true;
   }

   function viewCalendar($date, $onlyTable = false) {
      $parms = array('active' => 1);
      if (defined("LANG")) $parms['lang'] = LANG;
      $years = News::GetYears($parms);
      $months = Utils::GetMonths();
      $selectedDay = '';
      if ($date && preg_match('/^([0-9]{4})([0-9]{2})([0-9]{2})?$/', $date, $m)) {
         $currYear = $m[1];
         $currMonth = ($m[2]*1);
         $fixedDay = !empty( $m[ 3 ] );
         $currDay = ( int ) ( $m[3] ? $m[3] : 1 );
         $curDate = mktime(0, 0, 0, $currMonth, $currDay, $currYear);
         $lastDay = mktime(0, 0, 0, $currMonth, date("t", $curDate), $currYear);
         $firstDay = mktime(0, 0, 0, $currMonth, 1, $currYear);
      }
      else {
         $max = News::GetMaxDate($parms);
         $currYear = date("Y", $max);
         $currMonth = date("n", $max);
         $curDate = $max;
         $lastDay = mktime(0, 0, 0, date("n", $max), date("t", $max), date("Y", $max));
         $firstDay = mktime(0, 0, 0, date("n", $max), 1, date("Y", $max));
      }
      $parms['date_from'] = $firstDay;
      $parms['date_to'] = $lastDay;
      $currentDay = date("Ymd", time());
      $maxD = date("t", $curDate);
      $lastWeek = (date("W", $lastDay)*1);
      if ($lastWeek == 1) $lastWeek = ((date("W", $firstDay)*1) + 5);
      $firstWeek = (date("W", $firstDay)*1);
      if ($firstWeek > $lastWeek) $firstWeek = 0;
      $numW = $lastWeek - $firstWeek + 1;
      $countnews = News::GetCountCaledar($parms);
      $prevYear = $currYear;
      $prevMonth = ($currMonth - 1);
      if ($prevMonth <= 0) {
        $prevMonth = 12;
        $prevYear --;
      }
      $nextYear = $currYear;
      $nextMonth = ($currMonth + 1);
      if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear ++;
      }
      include_once(Site::GetParms('libPath').'Actions.php');
      include(Site::GetTemplate($this->name, 'calendar'.($onlyTable ? '-table' : '')));
      return true;
   }
   
   function viewOnlyCalendar() {
        foreach ($_POST as $k => $v) $_POST[$k] = addslashes(stripslashes(strip_tags($v)));
        if (!$_POST['month'] || !$_POST['year']) {
           echo 'Bad data';
           die;
        }
        $date = $_POST['year'].($_POST['month'] < 10 ? '0'.$_POST['month'] : $_POST['month']);
        return $this->viewCalendar($date, true);
   }
}
?>