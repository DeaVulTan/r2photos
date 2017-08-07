<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class VotesEngine {
   var $name; var $parms;
   function VotesEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $this->ip = Votesvars::get_ip();
        $parmsT = array('href' => 'votes');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Опросы'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['do'] == 1) return $this->doVotes();
      else if ($this->parms['id'] > 0) return $this->viewResult();
      else return $this->viewVotes();
   }

   function viewVotes() {
      define("MENU_CRUMBS_LASTLINK", false);
      $page = ($this->parms['page'] ? $this->parms['page'] : 1);
      $numVotesOnPage = Utils::GetValue('count_votes_on_page');
      $count = Votes::GetCountRows(array('show' => 1));
      if ($count > 0) {
         $numPages = ceil($count / $numVotesOnPage);
         $votes = Votes::GetRows(array('show' => 1), array('limit' => $numVotesOnPage, 'offset' => (($page - 1) * $numVotesOnPage)));
         if (sizeOf($votes) > 0) foreach ($votes as $k => $v) {
           $vars = Votesvars::GetRows(array('active' => 1, 'poll_id' => $v['id']));
           $isIP = Votesvars::isThisIPVotes(array('poll_id' => $v['id'], 'ip' => $this->ip));
           $votes[$k]['vars'] = $vars;
           $votes[$k]['isIP'] = $isIP;
         }
      }
      include(Site::GetTemplate($this->name, 'list'));
      return true;
   }

   function viewOnMainPage() {
      $vote = Votes::GetRandom(array('active' => 1, 'show' => 1));
      if (!$vote) $vote = Votes::GetRandom(array('show' => 1));
      if ($vote) {
        $vars =& Votesvars::GetRows(array('active' => 1, 'poll_id' => $vote['id']));
        $isIP = Votesvars::isThisIPVotes(array('poll_id' => $vote['id'], 'ip' => $this->ip));
      }
      include(Site::GetTemplate($this->name, 'main'));
      return true;
   }

   function viewResult() {
      define("MENU_CRUMBS_LASTLINK", true);
      if (!$vote = Votes::GetRow(array('id' => $this->parms['id']))) return false;
      if ($this->parms['href'] <> $vote['href']) return false;
      $isIP = Votesvars::isThisIPVotes(array('poll_id' => $vote['id'], 'ip' => $this->ip));
      if (!$vote['is_show'] || (!$isIP && $vote['is_active'])) return false;
      $vars = Votesvars::GetRows(array('active' => 1, 'poll_id' => $vote['id']));
      $total = Votesvars::SumQ(array('poll_id' => $vote['id']));
      Site::SetParms('bread-crumbs', array($vote['question']));
      include(Site::GetTemplate($this->name, 'result'));
      return true;
   }

   function doVotes() {
      if (is_array($_POST['votesResult']) && $_POST['votesId'] > 0 && preg_match("/".strtolower(PROJECT_NAME)."/", $_SERVER['HTTP_REFERER'])) {
         if (!$vote = Votes::GetRow(array('id' => (int)$_POST['votesId']))) return false;
         $isIP = Votesvars::isThisIPVotes(array('poll_id' => $vote['id'], 'ip' => $this->ip));
         if ($isIP || !$vote['is_show'] || !$vote['is_active']) return false;
         if ($vote['is_checkbox']) foreach ($_POST['votesResult'] as $var => $v) Votesvars::IncrementQ(array('poll_id' => $vote['id'], 'id' => $var));
         else foreach ($_POST['votesResult'] as $k => $v) Votesvars::IncrementQ(array('poll_id' => $vote['id'], 'id' => $v));
         Votesvars::setThisIPVotes(array('poll_id' => $vote['id']));
         return $this->menuPath.$this->name."/".$vote['id']."-".$vote['href'];
      }
      else return false;
   }
}
?>