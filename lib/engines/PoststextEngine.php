<?php
class PoststextEngine {
   var $name; var $parms;

   function PoststextEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      if ($this->parms['post_id']) return $this->viewPosttext();
      return true;
   }

   function viewPosttext() {
      if (!$posttext = Poststext::GetRow(array('post_id' => $this->parms["post_id"]))) return false;
      if (!$post = Posts::GetRow(array('id' => $this->parms["post_id"]))) return false;
      if (!$topic = Topics::GetRow(array('id' => $post["topic_id"]))) return false;
      if (!$forum = Forums::GetRow(array('id' => $topic["forum_id"]))) return false;
      if (!$cat = Categories::GetRow(array('active' => 1, 'id' => $forum["cat_id"]))) return false;
      $item = $post;
      $item['text'] = $posttext['text'];
      $item['text_id'] = $posttext['id'];
      $item['user'] = Users::GetRow(array('id' => $post['poster_id']));
      include(Site::GetTemplate('forum', 'posttext'));
      return true;
   }
}
?>