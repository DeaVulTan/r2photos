<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
include_once(Site::GetParms('tablesPath')."Orders.php");
include_once(Site::GetParms('tablesPath')."Corr.php");
include_once(Site::GetParms('tablesPath')."Ordersstatus.php");
class OrderstateEngine {
   var $name; var $parms;
   function OrderstateEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'orderstate');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Cостояние заказа'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
	   if(isset($_POST['exit'])){
			   setcookie('order','',time()-3600*7,'/');
			  header( 'Location: /orderstate', true, 301 );
			  
		   
	   }
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
	  if($this->parms['event']&&$this->parms['event']=='check'){
		  return $this->Check();
	  }
	  if(isset($_COOKIE['order'])){
		  
		  if($this->parms['event']&&$this->parms['event']=='add'){
			 return $this->Add();
		  }
		  return $this->viewOrderState($_COOKIE['order']);
	  }else{
	      return $this->viewIndex();	  
	  }

   }
   function viewIndex(){
		 $form =& $this->createFormIndex();
         include(Site::GetTemplate($this->name, 'index'));
		 return true;
   }
   function Check(){
	   $form =& $this->createFormIndex();
       if ($form->processIfSubmitted() && preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
		     $form_data = $form->get();

			//Проверка введенных данных
			if(!preg_match('/^[0-9].*$/', $form_data['code'])){
			 $alert = 'Укажите Ваш номер заказа!';
			 $form->assign($form_data);
			 include(Site::GetTemplate($this->name, 'index'));
			 return true;
			}
		   $order=Orders::GetRow(array('checkstatus'=>$form_data['code']));
			   
		   if($order){
			   setcookie('order',$order['id'],time()+3600*7,'/');
			 
			   
		   }else{
			 $alert = 'Данного заказа не существует!';
			 $form->assign($form_data);
			 include(Site::GetTemplate($this->name, 'index'));
			 return true;
		   }
		   
	   }
	   header( 'Location: /orderstate', true, 301 );
	   die();
   }
   function viewOrderState($orderID,$form){
	   
		 if(!isset($form)){
			$form =& $this->createFormQuest(); 
		 }
		 $number=Orders::GetNumbers(array('checkstatus'=>$orderID));
		 $order=Orders::GetRow(array('checkstatus'=>$orderID));
		 $corresp=Corr::GetRows(array('ord_id'=>$orderID,'active'=>1));
		 $state=Ordersstatus::GetRow(array('id'=>$order['status']));
		 include(Site::GetTemplate($this->name,'order-state'));
		 
		 return true;
	   
   }
   function Add(){
	   
	   $form =& $this->createFormQuest();
       if ($form->processIfSubmitted() && preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
		     $form_data = $form->get();
        
			//Проверка введенных данных
			if( trim($form_data['quest'])==''){
			 $alert = 'Укажите корректный вопрос!';
			 $form->assign($form_data);
			 $this->viewOrderState($_COOKIE['order'],$form);
			 return true;
			}
		   $form_data['ord_id']=$_COOKIE['order'];
		   $form_data['is_active']=1;
		   $model=&Corr::Get('',true);
		   $model->SetData($form_data);
		   $model->StoreRow();
		   
		   
	   }
	   header( 'Location: /orderstate', true, 301 );
	   die();
	   
	   
   }
   function &createFormIndex() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
               array(
                     new Input('code'),
                    ),
               array(
                  'name' => 'codeForm',
                  'id' => 'codeForm',
                  'data-validate-style' => 'alert',
                  'action' => (defined("LANG") ? URL_ADD : '').$this->menuPath.$this->name.'/check',
               )
            );
   }
   function &createFormQuest() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
               array(
                     new TextArea('quest'),
                    // new Capcha('code'),
                    ),
               array(
                  'name' => 'questForm',
                  'id' => 'questForm',
                  'data-validate-style' => 'alert',
                  'action' => (defined("LANG") ? URL_ADD : '').$this->menuPath.$this->name.'/add',
               )
            );
   }
}
?>