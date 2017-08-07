<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('tablesPath')."Certificatesorders.php");
class CertificatesEngine {
    var $name; var $parms;
    function CertificatesEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'certificates');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Подарочные сертификаты'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
    }

    function run() {
        if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
        if ($this->parms['action'] === 'order') return $this->viewForm();
        if ($this->parms['action'] === 'ok') return $this->viewOk();
        return $this->viewList();
    }

    function viewOk() {
        include( Site::GetTemplate( $this->name, 'ok' ) );
        if( Site::GetParms( 'isAjax' ) ) {
            die();
        }
        return true;
    }

    function viewForm() {
        if( !empty( $_POST ) ) {
            return $this->doClose();
        }
        $certificateId = ( int ) $this->parms['id'];
        $form = $this->createForm( $certificateId );
        include( Site::GetTemplate( $this->name, ( Site::GetParms( 'isAjax' ) ? 'form-popup' : 'form' ) ) );
        if( Site::GetParms( 'isAjax' ) ) {
            die();
        }
        return true;
    }//viewForm

    function createForm( $certificateId = 0 ) {
        include_once(Site::GetParms('libPath').'Form.php');
        return new Form(
            array(
               new Input('fio'),
               new Input('email'),
               new Input('phone'),
               new Input('city'),
               new Input('how'),
               new Select('certificate_type_id', array('items' => Certificatestypes::GetRows( array( 'certificate_id' => $certificateId ) ), 'field' => 'name')),
               new Select('delivery', array('items' => Utils::GetDeliveryList())),
               new TextArea('delivery_address'),
               new CheckBox('makeup'),
               new Select('location', array('items' => Utils::GetLocationList( true /* , $certificateId */ ))),
               new CheckBox('photobook'),
               new CheckBox('photopicture'),
               new Input('code'),
            ),
            array(
                'name' => $this->name.'Form',
                'id' => $this->name.'Form',
                'data-validate-style' => 'alert',
                'action' => $this->menuPath.$this->name.'/send'
            )
        );
    }//createForm

    private function doClose(){
     $certificateId = ( int ) $this->parms['id'];
     $form = $this->createForm( $certificateId );
     if( $form->processIfSubmitted() ){
       $locationId = ( int ) $_REQUEST['location'];
       $formTemplate = Site::GetTemplate( $this->name, ( Site::GetParms( 'isAjax' ) ? 'form-popup' : 'form' ) );
       $form_data = $form->get();
       
       if(!preg_match('/[a-zA-Zа-яА-Я]+/', $form_data['fio'])){
         $alert = 'Укажите Ваше имя!';
         unset($form_data['fio'], $form_data['code']);
         $form->assign($form_data);
         include( $formTemplate );
         die();
        }
   
        if(!preg_match('/[0-9]{3,}/', $form_data['phone'])){
         $alert = 'Укажите телефон!';
         unset($form_data['phone'], $form_data['code']);
         $form->assign($form_data);
         include( $formTemplate );
         die();
        }

       if (!Utils::CheckMouseMove()) {
         $alert = 'Укажите правильный проверочный код!';
         unset($form_data['code']);
         $form->assign($form_data);
         include( $formTemplate );
         die();
       }

       if( $form_data['delivery'] != 2 ) {
           $form_data['delivery_address'] = '';
       }

       include_once(Site::GetParms('libPath').'Mail.php');
       $form_data['subject'] = Utils::GetValue('subject_mail_after_send_mail');
       $form_data['city'] = Utils::CheckCityByIP();
       $form_data['delivery_list'] = Utils::GetDeliveryList();
       $form_data['location_list'] = Utils::GetLocationList();
       $form_data['certificate_id'] = $certificateId;
       $form_data['certificate'] = Certificates::GetRow( array( 'id' => $certificateId, 'active' => 1 ) );
       $form_data['certificate_type'] = Certificatestypes::GetRow( array( 'id' => $form_data['certificate_type_id'] ) );
       $form_data['location'] = $locationId;
       $mailFiles = array(
          'logo' => Site::GetParms('absolutePath').'image/logo.png',
          'pixel' => Site::GetParms('absolutePath').'image/0.gif'
       );

       $insert = array(
        array(
            'idate' => time(),
            'fio' => $form_data['fio'],
            'phone' => $form_data['phone'],
            'email' => $form_data['email'],
            'city' => $form_data['city'],
            'delivery' => $form_data['delivery'],
            'delivery_address' => $form_data['delivery_address'],
            'makeup' => $form_data['makeup'],
            'photobook' => $form_data['photobook'],
            'photopicture' => $form_data['photopicture'],
            'code' => $form_data['code'],
            'certificate_id' => $form_data['certificate_id'],
            'certificate_type_id' => $form_data['certificate_type_id'],
            'location' => $form_data['location'],
        ),
       );
       Certificatesorders::Save( $insert );

       foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
       $form_data['iwh'] = getimagesize($mailFiles['logo']);
       $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
       $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
       $message = MailMessage::GetFromTemplate(
                     array(
                        'FROM'    => Utils::GetValue('mailer_email'),
                        'TO'      => Utils::GetValue('certificates_email'),
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
       return $this->menuPath.$this->name.'/'.$certificateId.'/ok';
     }
    }

    function viewList() {
        if( !$this->parms['id'] ) {
            $first = reset( Certificates::GetRows( array( 'active' => 1 ), array( 'limit' => 1 ) ) );
            return '/certificates/'.( $first['id'] );
        }
        define("MENU_CRUMBS_LASTLINK", false);
        $itemsList = Certificates::GetRows(array('active' => 1));
        $page = ($this->parms['page'] ? $this->parms['page'] : 1);
        $numPhotosOnPage = ( int ) Utils::GetValue( 'count_certificates_photos_on_page' );
        $parms = array('active' => 1, 'certificate_id' => $this->parms['id']);
        if (defined("LANG")) $parms['lang'] = LANG;
        $count = Certificatesphotos::GetCountRows($parms);
        if ($count > 0) {
            $numPages = ceil($count / $numPhotosOnPage);
            $photosList = Certificatesphotos::GetRows($parms, array('limit' => $numPhotosOnPage, 'offset' => (($page - 1) * $numPhotosOnPage)));
        }
        $currentItem = $itemsList[ $this->parms['id'] ];

        //locations
        /* include_once( Site::GetParms( 'libPath' ).'Cataloglinksmgr.php' );
        $links = new Cataloglinksmgr();
        $locationsList = $links->getItemsLinksTo( $links->clType( 'certificates' ), $this->parms['id'], $links->clType( 'locations' ) );
        unset( $links ); */
        $locationsList = Locations::GetRows( array( 'active' => 1 ) );
        foreach( $locationsList as $locationId => $location ) {
            $locationsList[ $locationId ]['_photos'] = Locationsphotos::GetRows( array( 'active' => 1, 'location_id' => $location['id'] ) );
        }

        include( Site::GetTemplate( $this->name, 'list' ) );
        return true;
    }
    function onMain(){
	
        $itemsList = Certificates::GetRows(array('active' => 1));
        include( Site::GetTemplate( $this->name, 'on-main' ) );
	}
    function viewOne() {
        define("MENU_CRUMBS_LASTLINK", true);
        $parms = array('id' => $this->parms['id'], 'active' => 1);
        if (defined("LANG")) $parms['lang'] = LANG;
        if (!$new = Certificates::GetRow($parms)) return false;
        Site::SetParms('bread-crumbs', array('<a href="/certificates" title="Подарочные сертификаты">Подарочные сертификаты</a>', $new['name']));
        include(Site::GetTemplate($this->name, 'list'));
        return true;
    }
}
?>