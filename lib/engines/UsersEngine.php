<?php
include_once(Site::GetParms('tablesPath')."Topics.php");
class UsersEngine {
   var $name; var $parms;

   function UsersEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      if ($this->parms["mode"] == 'list') return $this->viewUserslist();
      else if (!$this->parms["mode"] && $this->parms["id"] > 0) return $this->viewUser();
      else if ($this->parms["mode"] == 'topics' && $this->parms["id"] > 0) return $this->viewUserstopic();
      else if ($this->parms["mode"] == "mail") return $this->viewFormMail();
      else if ($this->parms["mode"] == "send") return $this->doMailUser();
      else if ($this->parms["mode"] == "ok") return $this->viewOkMailUser();
      else if ($this->parms["mode"] == 'profile' && $this->parms["id"] > 0) return $this->viewUserprofile();
      else if ($this->parms["mode"] == "profilechange") return $this->changeUserprofile();
      else return false;
   }

   function viewUserslist() {
      $page = (int)($this->parms["page"] ? $this->parms['page'] : 1);
      $numOnPage = Utils::GetValue('users_per_page');
      $count = Users::GetCountRows(array('active' => 1));
      $numPages = ceil($count / $numOnPage);
      $users =& Users::GetRows(array('active' => 1), array('limit' => $numOnPage, 'offset' => (($page - 1) * $numOnPage)));
      include(Site::GetTemplate('forum', 'user-list'));
      return true;
   }

   function viewUser() {
      if (!$user =& Users::GetRow(array('id' => $this->parms["id"]))) return false;
      $topics_count =& Topics::GetCountRows(array('poster' => $user['id']));
      include(Site::GetTemplate('forum', 'user'));
      return true;
   }

   function viewUserstopic() {
      if (!$user =& Users::GetRow(array('id' => $this->parms["id"]))) return false;
      $topics =& Topics::GetRows(array('poster' => $user["id"]));
      $posts =& Posts::GetRows(array('poster_id' => $user["id"]));
      include(Site::GetTemplate('forum', 'user-topics'));
      return true;
   }

   function viewFormMail() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (!$user = Users::GetRow(array('idmd5' => $this->parms["id"]))) return false;
      $form =& $this->createFormMail();
      $form->set('userid', $this->parms["id"]);
      include(Site::GetTemplate('forum', 'formmail'));
      return true;
   }

   function &createFormMail() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(
               new Input('userid', array('type' => 'hidden')),
               new TextArea('text', array('regexp' => '/.+/i', 'alert' => 'Укажите текст сообщения!'))
              ),
         array(
            'name' => 'sendMailForm',
            'action' => 'usermail_send.htm',
            'function' => 'fnCheckSendUserMailForm'
         )
      );
   }

   function doMailUser() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (!$userFrom = Users::GetRow(array('id' => UID))) return false;
      if (!$userTo = Users::GetRow(array('idmd5' => $_POST['userid']))) return false;
      $form =& $this->createFormMail();
      if ($form->processIfSubmitted() && preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
         $form_data = $form->get();
         foreach ($form_data as $k => $v) $form_data[$k] = strip_tags($v);
         $form_data['from'] = $userFrom['nikname'];
         include_once(Site::GetParms('libPath').'Mail.php');
         $form_data['subject'] = Utils::GetValue('subject_mail_after_mail_forum');
         $mailFiles = array(
            'logo' => Site::GetParms('absolutePath').'data/image/subscribers/logo.gif',
            'pixel' => Site::GetParms('absolutePath').'image/0.gif'
         );
         foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
         $form_data['iwh'] = getimagesize($mailFiles['logo']);
         $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
         $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
         $message =& MailMessage::GetFromTemplate(
                     array(
                        'FROM'    => $userFrom['email'],
                        'TO'      => $userTo['email'],
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
         return Site::CreateUrl('usermail_ok');
      }
      else return Site::CreateUrl('usermail_'.$_POST['userid']);
   }

   function viewOkMailUser() {
      include(Site::GetTemplate('forum', 'mailok'));
      return true;
   }

   function viewUserprofile() {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (defined("UID") && UID > 0 && UID <> $this->parms["id"]) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (!$user =& Users::GetRow(array('id' => $this->parms["id"]))) return false;
      include(Site::GetTemplate('forum', 'user-profile'));
      return true;
   }

   function changeUserprofile () {
      if (!defined("UID") || (defined("UID") && !UID)) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (defined("UID") && UID > 0 && UID <> $_POST["userId"]) return '/login?refer='.urlencode(Site::GetParms('scriptName'));
      if (!$user =& Users::GetRow(array('id' => UID))) return false;
      $avatar_uploaded = false;
      if (is_uploaded_file($_FILES['avatar']["tmp_name"])) {
         preg_match('/\.([^\.]+)$/', $_FILES['avatar']["name"], $m);
         $fileName = "data/image/users/users_".UID.".".$m[1];
         $adr = Site::GetParms('absolutePath').$fileName;
         $adr_tmp = Site::GetParms('absolutePath')."data/image/users/users_tmp_".UID.".".$m[1];
         move_uploaded_file($_FILES['avatar']["tmp_name"], $adr_tmp);
         if ($size = GetImageSize($adr_tmp)) {
            if ($size[0] <= 110 && $size[1] <= 110) {
               rename($adr_tmp, $adr);
               $avatar_uploaded = true;
            }
         }
      }
      $tmp[UID] = array("email" => $_POST["email"], "phones" => $_POST["phones"], "sig" => $_POST["sig"]);
      if (isset($_POST['nikname']) && mb_strlen($_POST['nikname'], 'utf-8')) $tmp[UID]['nikname'] = $_POST['nikname'];
      if ($avatar_uploaded) $tmp[UID]['avatar'] = $fileName;
      Users::Update($tmp);
      return Site::CreateUrl('users_profile_'.UID);
   }
}
?>
