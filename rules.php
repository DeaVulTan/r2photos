<?php
$rules = array(
'(index)?' => array('Login' => array(), 'Layout' => array('path' => 'main')),

'((.+)\/)?sitemap' => array('Login' => array(), 'Sitemap' => array(), 'Layout' => array('path' => 'work')),

'((.+)\/)?catalog(\/([1-9][0-9]*)-([a-zA-Z0-9\-]+))?(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Catalog' => array('id' => '{4}', 'href' => '{5}', 'page' => '{7}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?fabrics(\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)(\/page\/([1-9][0-9]*))?)?' => array('Login' => array(), 'Catalog' => array('action' => 'fabrics', 'idF' => '{4}', 'href' => '{5}', 'page' => '{7}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?catalog-fabrics\/([1-9][0-9]*)\/([1-9][0-9]*)(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Catalog' => array('action' => 'cf', 'idC' => '{3}', 'idF' => '{4}', 'page' => '{6}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?fabrics-catalog\/([1-9][0-9]*)\/([1-9][0-9]*)(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Catalog' => array('action' => 'fc', 'idF' => '{3}', 'idC' => '{4}', 'page' => '{6}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?item\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)' => array('Login' => array(), 'Catalog' => array('item_id' => '{3}', 'href' => '{4}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?certificates(\/([1-9][0-9]*))?(\/(order|ok))?(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Certificates' => array('id' => '{4}', 'action' => '{6}', 'page' => '{8}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?locations' => array('Login' => array(), 'Locations' => array(), 'Layout' => array('path' => 'work')),
'((.+)\/)?primery-retushi' => array('Login' => array(), 'Retouches' => array(), 'Layout' => array('path' => 'work')),
'obrabotka-fotografii'=>array('Login' => array(), 'Retouches' => array(), 'Layout' => array('path' => 'work')),

'((.+)\/)?photographers(\/([1-9][0-9]*)-([a-zA-Z0-9\-]+))?(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Photographers' => array('catalog_id' => '{4}', 'href' => '{5}', 'page' => '{7}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?photographers\/([1-9][0-9]*)(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Photographers' => array('id' => '{3}', 'page' => '{5}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?news(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'News' => array('page' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?news\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)' => array('Login' => array(), 'News' => array('id' => '{3}', 'href' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?news\/archiv(\/([0-9]{6,8}))?' => array('Login' => array(), 'News' => array('action' => 'archiv', 'date' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?news\/viewcalendar' => array('Login' => array(), 'News' => array('action' => 'viewcalendar')),

'((.+)\/)?actions(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Actions' => array('page' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?actions\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)' => array('Login' => array(), 'Actions' => array('id' => '{3}', 'href' => '{4}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?articles(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Articles' => array('page' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?articles\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)' => array('Login' => array(), 'Articles' => array('id' => '{3}', 'href' => '{4}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?faq(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Faq' => array('page' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?faq\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)' => array('Login' => array(), 'Faq' => array('id' => '{3}', 'href' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?faq\/add' => array('Login' => array(), 'Faq' => array('add' => 1), 'Layout' => array('path' => 'work')),
'((.+)\/)?faq\/ok' => array('Login' => array(), 'Faq' => array('action' => 'ok'), 'Layout' => array('path' => 'work')),

'((.+)\/)?photo(\/([1-9][0-9]*)-([a-zA-Z0-9\-]+))?' => array('Login' => array(), 'Photo' => array('part_id' => '{4}', 'href' => '{5}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?search' => array('Login' => array(), 'Search' => array(), 'Layout' => array('path' => 'work')),

'((.+)\/)?orderstate' => array('Login' => array(), 'Orderstate' => array(), 'Layout' => array('path' => 'work')),
'((.+)\/)?orderstate\/check' => array('Login' => array(), 'Orderstate' => array('event'=>'check'), 'Layout' => array('path' => 'work')),
'((.+)\/)?orderstate\/add' => array('Login' => array(), 'Orderstate' => array('event'=>'add'), 'Layout' => array('path' => 'work')),

'((.+)\/)?opinions(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Opinions' => array('page' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?opinions\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)' => array('Login' => array(), 'Opinions' => array('id' => '{3}', 'href' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?opinions\/add' => array('Login' => array(), 'Opinions' => array('add' => 1), 'Layout' => array('path' => 'work')),
'((.+)\/)?opinions\/ok' => array('Login' => array(), 'Opinions' => array('action' => 'ok'), 'Layout' => array('path' => 'work')),

'((.+)\/)?votes(\/page\/([1-9][0-9]*))?' => array('Login' => array(), 'Votes' => array('page' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?votes\/([1-9][0-9]*)-([a-zA-Z0-9\-]+)' => array('Login' => array(), 'Votes' => array('id' => '{3}', 'href' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?votes\/do' => array('Votes' => array('do' => 1)),

'((.+)\/)?licenses' => array('Login' => array(), 'Licenses' => array(), 'Layout' => array('path' => 'work')),

'((.+)\/)?partners(\/([0-9]+)-([a-zA-Z0-9\-]+))?' => array('Login' => array(), 'Partners' => array('id' => '{4}', 'href' => '{5}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?vacancies(\/([1-9][0-9]*)-([a-zA-Z0-9\-]+))?' => array('Login' => array(), 'Vacs' => array('id' => '{4}', 'href' => '{5}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?registration(\/(ok|add|also|end|del))?' => array('Login' => array(), 'Registration' => array('action' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?registration\/([a-zA-Z]+)\/([a-zA-ZA-Z0-9]+)' => array('Login' => array(), 'Registration' => array('action' => '{3}', 'id' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?(login|forgot|forgot\/ok|cabinet|cabinet\/ok)' => array('Login' => array(), 'Cabinet' => array('action' => '{3}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?cabinet\/(change|forgotsearch|changepassword)' => array('Login' => array(), 'Cabinet' => array('action' => '{3}')),
'((.+)\/)?cabinet\/(form|orders|discount|subscribe)' => array('Login' => array(), 'Cabinet' => array('action' => '{3}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?logout' => array('Logout' => array()),

'((.+)\/)?social(\/(confirm|init))?\/(vk|fb|tw|od)' => array('Login' => array(), 'Social' => array('action' => '{4}', 'type' => '{5}')),

'((.+)\/)?subscribers\/do' => array('Subscribers' => array('action' => 'do')),
'((.+)\/)?subscribers(\/(want|enter|also|del|ok|inbase))?' => array('Login' => array(), 'Subscribers' => array('action' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?subscribers\/([a-zA-Z]+)\/([a-zA-ZA-Z0-9]+)' => array('Login' => array(), 'Subscribers' => array('action' => '{3}', 'id' => '{4}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?activation\/send' => array('Login' => array(), 'Activation' => array('action' => 'send'), 'Layout' => array('path' => 'work')),
'((.+)\/)?activation(\/(ok))?' => array('Login' => array(), 'Activation' => array('action' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?callback\/send' => array('Login' => array(), 'Callback' => array('action' => 'send'), 'Layout' => array('path' => 'work')),
'((.+)\/)?callback(\/(ok))?' => array('Login' => array(), 'Callback' => array('action' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?ordersphotographer\/([1-9][0-9]*)' => array('Login' => array(), 'Ordersphotographer' => array('action' => 'form', 'id' => '{3}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?ordersphotographer\/send' => array('Login' => array(), 'Ordersphotographer' => array('action' => 'send'), 'Layout' => array('path' => 'work')),
'((.+)\/)?ordersphotographer(\/(ok))?' => array('Login' => array(), 'Ordersphotographer' => array('action' => '{4}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?mail\/send' => array('Login' => array(), 'Mail' => array('action' => 'send'), 'Layout' => array('path' => 'work')),
'((.+)\/)?mail(\/(ok))?' => array('Login' => array(), 'Mail' => array('action' => '{4}'), 'Layout' => array('path' => 'work')),

'((.+)\/)?orders(\/(form|ok)(\/([1-9][0-9]*))?)?' => array('Login' => array(), 'Orders' => array('action' => '{4}', 'ok_id' => '{6}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?orders\/(do|update|close|del)(\/(.+))?' => array('Login' => array(), 'Orders' => array('action' => '{3}', 'id' => '{5}'), 'Layout' => array('path' => 'work')),
'((.+)\/)?orders\/doajax' => array('Login' => array(), 'Orders' => array('action' => 'doajax')),


'(o-fotostudii\/nashi-fotografii)' => array('Login' => array(), 'Portfolio' => array(), 'Layout' => array('path' => 'work')),
'((.+)\/)?photos'  => array('Login' => array(), 'Portfolio' => array('action' => 'photos-lazy-load'), 'Layout' => array('path' => 'work')),
'((.+)\/)?photos\/preload\/([1-9][0-9]*)'  => array('Login' => array(), 'Portfolio' => array('action' => 'load-more-photos', 'start' => '{3}')),

'(forum|cat_([1-9][0-9]*))\.htm' => array('Login' => array(), 'Categories' => array('category' => '{2}'), 'Layout' => array('path' => 'work')),
'forum(_(moderate))?_([1-9][0-9]*)(_([1-9][0-9]*))?\.htm' => array('Login' => array(), 'Forums' => array('mode' => '{2}', 'id' => '{3}', 'page' => '{5}'), 'Layout' => array('path' => 'work')),
'topic(_(new|moderate))?_([1-9][0-9]*)(_([1-9][0-9]*))?\.htm' => array('Login' => array(), 'Topics' => array('mode' => '{2}', 'topic' => '{3}', 'page' => '{5}'), 'Layout' => array('path' => 'work')),
'topic_(add|change)\.htm' => array('Login' => array(), 'Topics' => array('mode' => '{1}')),
'post_(new|edit)_([1-9][0-9]*)(_([1-9][0-9]*))?\.htm' => array('Login' => array(), 'Posts' => array('mode' => '{1}', 'id' => '{2}', 'post' => '{4}'), 'Layout' => array('path' => 'work')),
'post_(changeself|add|change|delete)(_([1-9][0-9]*))?\.htm' => array('Login' => array(), 'Posts' => array('mode' => '{1}', 'id' => '{3}')),
'posttext_([1-9][0-9]*)\.htm' => array('Login' => array(), 'Poststext' => array('post_id' => '{1}'), 'Layout' => array('path' => 'work')),
'users_list(_([1-9][0-9]*))?\.htm' => array('Login' => array(), 'Users' => array('mode' => 'list', 'page' => '{2}'), 'Layout' => array('path' => 'work')),
'users(_(topics|profile))?_([1-9][0-9]*)\.htm' => array('Login' => array(), 'Users' => array('mode' => '{2}', 'id' => '{3}'), 'Layout' => array('path' => 'work')),
'users_profilechange\.htm' => array('Login' => array(), 'Users' => array('mode' => 'profilechange')),
'usermail_send\.htm' => array('Login' => array(), 'Users' => array('mode' => 'send')),
'usermail_ok\.htm' => array('Login' => array(), 'Users' => array('mode' => 'ok'), 'Layout' => array('path' => 'work')),
'usermail_([a-zA-ZA-Z0-9]+)\.htm' => array('Login' => array(), 'Users' => array('mode' => 'mail', 'id'=> '{1}'), 'Layout' => array('path' => 'work')),

'city\/(find|change)' => array('City' => array('action' => '{1}')),

'(o-fotostudii\/kontakty)' => array('Login' => array(), 'Text' => array('page' => '{1}', 'show_map' => true), 'Layout' => array('path' => 'work')),
'(.+)' => array('Login' => array(), 'Text' => array('page' => '{1}'), 'Layout' => array('path' => 'work')),
);
?>