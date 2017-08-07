<?php
class PostsEngine {
   var $name; var $parms;

   function PostsEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
     if ($this->parms['mode'] == 'add') return $this->doAdd();
     else if ($this->parms['mode'] == 'change') return $this->doChange();
     else if ($this->parms['mode'] == 'new') return $this->newPost();
     else if ($this->parms['mode'] == 'edit') return $this->viewEdit();
     else if ($this->parms['mode'] == 'delete') return $this->doDelete();
     else if ($this->parms['mode'] == 'changeself') return $this->doChangeSelf();
     else return false;
   }

   function newPost() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (!$topic = Topics::GetRow(array('id' => $this->parms['id']))) return false;
      if (!$forum = Forums::GetRow(array('id' => $topic['forum_id']))) return false;
      if (!$cat = Categories::GetRow(array('active' => 1, 'id' => $forum['cat_id']))) return false;
      if ($this->parms["post"]) $text = Poststext::GetPostText($this->parms["post"]);
      include(Site::GetTemplate('forum', 'post-new'));
      return true;
   }

   function viewEdit() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (!$post = Posts::GetRow(array('id' => $this->parms['id']))) return false;
      if (UID <> $post['poster_id']) return false;
      if (!($posttext = Poststext::GetRow(array('post_id' => $this->parms['id'])))) return false;
      if (!$topic = Topics::GetRow(array('id' => $post["topic_id"]))) return false;
      if (!$forum = Forums::GetRow(array('id' => $post["forum_id"]))) return false;
      if (!$cat = Categories::GetRow(array('active' => 1, 'id' => $forum["cat_id"]))) return false;
      include(Site::GetTemplate('forum', 'post-edit'));
      return true;
   }

   function doChangeSelf() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer=post_edit_'.$_POST['post_id'];
      if (!$post = Posts::GetRow(array('id' => $_POST['post_id']))) return false;
      if (UID <> $post['poster_id']) return false;
      if (!$posttext = Poststext::GetRow(array('post_id' => $_POST['post_id']))) return false;
      $tmp[$posttext['id']]['text'] = trim($_POST['text']);
      Poststext::Update($tmp);
      return 'topic_'.$post['topic_id'].'.htm';
   }

   function doDelete() {
      if (!defined("UID") || (defined("UID") && !UID)) {
         preg_match('/\/([^\/]+)$/', $_SERVER['HTTP_REFERER'], $m);
         return '/login?refer='.urlencode($m[1]);
      }
      if (!$post = Posts::GetRow(array('id' => $this->parms['id']))) return false;
      if (UID <> $post['poster_id']) return false;
      if (!$posttext = Poststext::GetRow(array('post_id' => $this->parms['id']))) return false;
      $maxBeforeDel = Posts::GetMaxPostID(array('topic_id' => $post["topic_id"]));
      Users::DecPosts(UID, 1);
      $tmp = array($this->parms['id']);
      Posts::Delete($tmp);
      $tmp = array($posttext['id']);
      Poststext::Delete($tmp);
      if ($this->parms['id'] == $maxBeforeDel) {
         // lastpost_id после удаления сообщения
         $forums = Forums::Get($post["forum_id"], true);
         $forums->SetValue('lastpost_id', Posts::GetMaxPostID(array('forum_id' => $post["forum_id"])));
         $forums->StoreRow();
         $topic = Topics::Get($post["topic_id"], true);
         $topic->SetValue('lastpost_id', Posts::GetMaxPostID(array('topic_id' => $post["topic_id"])));
         $topic->StoreRow();
      }
      return 'topic_'.$post['topic_id'].'.htm';
   }

   function doAdd() {
      if (!defined("UID") || (defined("UID") && !UID)) {
         preg_match('/\/([^\/]+)$/', $_SERVER['HTTP_REFERER'], $m);
         Site::SetSession('textAddPostForum', strip_tags(trim($_POST["text"])));
         return '/login?refer='.urlencode($m[1]);
      }
      $topic_id = $_POST["topic_id"];
      if (!$topic = Topics::GetRow(array('id' => $topic_id))) return false;
      if (!$forum = Forums::GetRow(array('id' => $topic["forum_id"]))) return false;
      // инкримент колл-ва постов юзера при добавлении нового сообщения
      $user = Users::Get(UID, true);
      $user->SetValue('posts', ($user->GetValue('posts') + 1));
      $user->StoreRow();
   	//запись в posts
      $posts =& Posts::Get('', true);
      $dataPosts = array('topic_id' => $topic_id, 'forum_id' => $forum['id'], 'poster_id' => UID, 'ip' => getenv("REMOTE_ADDR"), 'time' => time());
     	$posts->SetData($dataPosts);
      $idNew = $posts->StoreRow();
      // установка lastpost_id при добавлении темы
      $forums = Forums::Get($forum['id'], true);
      $forums->SetValue('lastpost_id', $idNew);
      $forums->StoreRow();
      $tmp[$topic_id] = array('lastpost_id' => $idNew);
      Topics::Update($tmp);
   	//запись в poststext
      $poststext =& Poststext::Get('', true);
      $dataPostsText = array('post_id' => $idNew, 'text' => strip_tags(trim($_POST["text"])));
      $poststext->SetData($dataPostsText);
      $poststext->StoreRow();
     	return Site::CreateUrl('topic_'.$topic_id);
   }

  function doChange() {
      $topic_id = $_POST["topic_id"];
      $post_id = $_POST["post_id"];
      $post_text_id = $_POST["post_text_id"];
      if (!$_POST["is_del"]) {
	      $tmp[$post_text_id] = array('text' => trim($_POST["text"]));
	      Poststext::Update($tmp);
	      return Site::CreateUrl('topic_moderate_'.$topic_id);
      }
      else {
	      if (!$post = Posts::GetRow(array('id' => $post_id))) return false;
	      if (!$topic = Topics::GetRow(array('id' => $topic_id))) return false;
	      if (!$user = Users::GetRow(array('id' => $post["poster_id"]))) return false;
	      // декримент колличества постов
         Users::DecPosts($user["id"], 1);
         $maxBeforeDel = Posts::GetMaxPostID(array('topic_id' => $topic_id));
	      // удаление параметров поста и текста поста
	      $tmp = array($post_id);
         Posts::Delete($tmp);
	      $tmp = array($post_text_id);
	      Poststext::Delete($tmp);
	      // декримент параметра lastpost_id
         if ($post_id == $maxBeforeDel) {
            $forums = Forums::Get($topic["forum_id"], true);
            $forums->SetValue('lastpost_id', Posts::GetMaxPostID(array('forum_id' => $topic["forum_id"])));
            $forums->StoreRow();
            $topic = Topics::Get($topic_id, true);
            $topic->SetValue('lastpost_id', Posts::GetMaxPostID(array('topic_id' => $topic_id)));
            $topic->StoreRow();
         }
	      return Site::CreateUrl('topic_moderate_'.$topic_id);
      }
  }
}
?>