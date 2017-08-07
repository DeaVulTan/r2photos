<?php
class TopicsEngine {
   var $name; var $parms;
   function TopicsEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      if ($this->parms['mode'] == 'add') return $this->doAdd();
      else if ($this->parms['mode'] == 'change') return $this->doChange();
      else if ($this->parms['mode'] == 'new') return $this->newTopic();
      else return $this->viewTopic();
   }

   function viewOnMainPage() {
      $posts = Posts::GetRows(array(), array('limit' => Utils::GetValue('count_forums_on_main_page')), true);
      include(Site::GetTemplate('forum', 'main'));
      return true;
   }

   function viewTopic() {
      if (!$topic = Topics::GetRow(array('id' => $this->parms['topic']))) return false;
      if (!$forum = Forums::GetRow(array('id' => $topic['forum_id']))) return false;
      if (!$cat = Categories::GetRow(array('active' => 1, 'id' => $forum['cat_id']))) return false;
      //проверка админ/модератор
      $moderator = ($forum["moderator"] == UID ? true : false);
      if ($this->parms["mode"] == 'moderate' && !$moderator) return '/login?refer=topic_'.$this->parms["topic"].'.htm';
      $moderate = ($this->parms["mode"] == 'moderate' && $moderator ? true : false);
      //инкремент числа просмотров
      $temp[$this->parms["topic"]] = array('views' => ($topic["views"] + 1));
      Topics::Update($temp);
      $page = (int)($this->parms["page"] ? $this->parms['page'] : 1);
      $numOnPage = Utils::GetValue('posts_per_page');
      $count = Posts::GetCountRows(array('topic_id' => $this->parms["topic"]));
      $numPages = ceil($count / $numOnPage);
      $posts = Posts::GetRows(array('topic_id' => $this->parms["topic"]), array('limit' => $numOnPage, 'offset' => (($page - 1) * $numOnPage)));
      if (sizeOf($posts) > 0) foreach ($posts as $id => $post) {
         $tmp =& Poststext::GetRow(array('post_id' => $id));
         $posts[$id]['text'] = $tmp['text'];
         $posts[$id]['text_id'] = $tmp['id'];
         $posts[$id]['user'] = Users::GetRow(array('id' => $post['poster_id']));
      }
      include(Site::GetTemplate('forum', 'topics'));
      return true;
   }

   function newTopic() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (!$forum =& Forums::GetRow(array('id' => $this->parms["topic"]))) return false;
      if (!$cat =& Categories::GetRow(array('id' => $forum["cat_id"]))) return false;
      $form =& $this->createForm();
      include(Site::GetTemplate('forum', 'topic-new'));
      return true;
   }

   function createForm() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(
               new Input('title', array('regexp' => '/(.+)/', 'alert' => 'Введите заголовок')),
               new Textarea('text', array('regexp' => '/(.+)/', 'alert' => 'Введите текст сообщения')),
              ),
         array(
            'name' => 'topicForm',
            'action' => 'topic_add.htm',
            'function' => 'fnCheckAddNewTopicForm',
         )
      );
   }

   function doAdd() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      $forum_id = $_POST["forum_id"];
      $form =& $this->createForm();
      if ($form->processIfSubmitted() && preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
         $form_data = $form->get();
         foreach ($form_data as $k => $v) $form_data[$k] = strip_tags($v);
         // инкримент колл-ва постов юзера при добавлении новой темы
         $user = Users::Get(UID, true);
         $user->SetValue('posts', ($user->GetValue('posts') + 1));
         $user->StoreRow();
         //запись в topics
         $topics =& Topics::Get('', true);
         $dataTopic = array('forum_id' => $forum_id, 'title' => $form_data['title'], 'poster' => UID, 'time' => time());
         $topics->SetData($dataTopic);
         $idNewT = $topics->StoreRow();
         //запись в posts
         $posts =& Posts::Get('', true);
         $dataPost = array('topic_id' => $idNewT, 'forum_id' => $forum_id, 'poster_id' => UID, 'subject' => $form_data["title"], 'ip' => getenv("REMOTE_ADDR"), 'time' => time());
         $posts->SetData($dataPost);
         $idNew = $posts->StoreRow();
         // установка lastpost_id при добавлении темы
         $tmp[$forum_id] = array('lastpost_id' => $idNew);
         Forums::Update($tmp);
         unset($tmp);
         $tmp[$idNewT] = array('lastpost_id' => $idNew);
         Topics::Update($tmp);
         //запись в poststext
         $poststext =& Poststext::Get('', true);
         $dataText = array('post_id' => $idNew, 'subject' => $form_data["title"], 'text' => $form_data["text"]);
         $poststext->SetData($dataText);
         $poststext->StoreRow();
         return Site::CreateUrl('topic_'.$idNewT);
      }
      else return Site::CreateUrl('topic_new_'.$forum_id);
   }

   function doChange() {
      // чекбоксы на удаление не выделены менять текст темы
      if (!$_POST["topic_id"]) foreach ($_POST["topic"] as $key => $topic) {
         $tmp[$key] = array('title' => $topic);
         Topics::Update($tmp);
         unset($tmp);
      }
      // чекбоксы на удаление выделены - удалить темы
      else {
         // уменьшение user_posts для всех post in topic
         $posts =& Posts::GetRows(array('topic_id_in' => implode(", ", array_keys($_POST["topic_id"]))));
         $users = array();
         if (sizeOf($posts) > 0) foreach ($posts as $post) $users[$post["poster_id"]] += 1;
         if (sizeOf($users) > 0) foreach ($users as $idU => $count) Users::DecPosts($idU, $count);
         // удаление параметров постов и текстов внутри поста
         $postsKeys = array_keys($posts);
         Posts::Delete($postsKeys);
         Poststext::DeletePosts(array("post_id_in" => implode(", ", $postsKeys)));
         // удаление темы
         $tmp = array_keys($_POST['topic_id']);
         Topics::Delete($tmp);
         unset($tmp);
         // lastpost_id после удаления темы
         $tmp[$_POST["forum_id"]] = array('lastpost_id' => Posts::GetMaxPostID(array('forum_id' => $_POST["forum_id"])));
         Forums::Update($tmp);
      }
      return Site::CreateUrl('forum_moderate_'.$_POST["forum_id"]);
      return true;
   }
}
?>
