<?php
class VotesvarsEngine extends AdminEngine {
   function VotesvarsEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['poll_id'] = Site::GetSession($this->name."-poll_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Votesvars::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['poll_id'] = Site::GetSession($this->name."-poll_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Votesvars::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-poll_id', ($_POST['fromFF'] ? $_POST['poll_id'] : $this->values[0]));
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function doBeforeRun() {
      if (IS_VOTES !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }

   function topNav() {
      return '<a href="votes-list.htm" title="Голосование">Голосование</a> &gt; Варианты ответов';
   }
}
?>