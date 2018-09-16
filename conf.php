<?php
$TGBot->settings(['disable_web_page_preview' => false,
'parse_mode' => 'HTML', //can be markdown 
'PostgreSQL' => false, 
'MySQL' => false,
'adminMySQL' => false, //Use MySQL for global post etc
'adminPostGreSQL' => false, //Use PostGreSQL for global post etc
'admins' => [
    451540728,
    432234432,
    242343244
]
]);
$TGBot->PostgreDBCredentials('localhost', 'USERNAME', 'PASSWORD', 'DATABASE');
$TGBot->MySQLDBCredentials('localhost', 'USERNAME', 'PASSWORD', 'DATABASE');
