<?php
$rules = array(
'(index\.htm)?' => array('Login' => array(), 'Layout' => array('path' => 'common')),
'login\.htm' => array('Page' => array('page' => "login.htm"), 'Layout' => array('path' => 'login')),
'logout\.htm' => array('Logout' => array()),
'orders-(newblank)' => array('Login' => array(), 'Orders' => array('action' => '{1}')),
'subscribersmain-(view)_([0-9]+)\.htm' => array('Login' => array(), 'Subscribersmain' => array('action' => '{1}', 'parms' => '{2}'), 'Layout' => array('path' => 'mailer')),
'search\.htm' => array('Login' => array(), 'Search' => array(), 'Layout' => array('path' => 'common')),
'cataloglinks-([a-z]+)(_([0-9]+))?(_([0-9]+))?(_([0-9]+))?\.htm' => array('Login' => array(), 'Cataloglinks' => array('action' => '{1}', 'item_type' => '{3}', 'item_id' => '{5}', 'target_type' => '{7}'), 'Layout' => array('path' => 'common')),
'ADMIN-order\.htm' => array('Login' => array(), 'ADMIN' => array('action' => 'order')),
'ADMIN-([a-z]+)(_([a-z0-9_]+))?\.htm' => array('Login' => array(), 'ADMIN' => array('action' => '{1}', 'parms' => '{3}'), 'Layout' => array('path' => 'common')),
'(.+)' => array('Login' => array(), 'Page' => array('page' => '404.htm'), 'Layout' => array('path' => 'common')),
);
?>