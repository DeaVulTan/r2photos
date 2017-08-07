<?php
class ForumsEngine {
   var $name; var $parms;
   function ForumsEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }
   
   function run() {
      return $this->viewForums();
   }
   
   function viewForums() {
      if (!$forum =& Forums::GetRow(array('id' => $this->parms["id"]))) return false;
      if (!$cat =& Categories::GetRow(array('id' => $forum["cat_id"]))) return false;
      $moderator = ($forum["moderator"] == UID ? true : false);
      if ($this->parms["mode"] == 'moderate' && !$moderator) return '/login?refer=forum_'.$this->parms["id"].'.htm';
      $moderate = ($this->parms["mode"] == 'moderate' && $moderator ? true : false);
      $page = (int)($this->parms["page"] ? $this->parms['page'] : 1);
      $numOnPage = Utils::GetValue('topics_per_page');
      $count = Topics::GetCountRows(array('forum_id' => $this->parms["id"]));
      $numPages = ceil($count / $numOnPage);
      $topics =& Topics::GetRows(array('forum_id' => $this->parms["id"]), array('limit' => $numOnPage, 'offset' => (($page - 1) * $numOnPage)));
      //get first messages
      foreach($topics as $id => $item) {
        $post = Posts::GetRow(array('topic_id' => $item['id']));
        $post = Poststext::GetRow(array('post_id' => $post['id']));
        $topics[$id]['first_post'] = $post;
        $user =& Users::GetRow(array('id' => $item["poster"]));
        $topics[$id]['user'] = $user;
      }
      include(Site::GetTemplate('forum', 'forums'));
      return true;
   }
}
?>