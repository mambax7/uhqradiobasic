<?php

// Modular Definitions

$modversion['name'] = _MI_UHQRADIOBASIC_NAME;
$modversion['version'] = 1.10;
$modversion['description'] = _MI_UHQRADIOBASIC_DESC;
$modversion['author'] = "Ian A. Underwood";
$modversion['credits'] = "Underwood Headquarters";
$modversion['help'] = "uhqradiobasic.html";
$modversion['license'] = "CC-GNU GPL";
$modversion['official'] = 0;
$modversion['image'] = "images/uhq_radiobasic_slogo.png";
$modversion['dirname'] = "uhq_radiobasic";

// Configuration Items

$modversion['hasConfig'] = 1;

$modversion['config'][] = array (
	'name'			=> 'cache_time',
	'title'			=> '_MI_UHQRADIOBASIC_OPT_CACHE_TIME',
	'description'	=> '_MI_UHQRADIOBASIC_OPT_CACHE_TIME_DESC',
	'formtype'		=> 'textbox',
	'valuetype'		=> 'int',
	'default'		=> 10
);

// Administrative Items

$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

// Menu Items

$modversion['hasMain'] = 0;

// Blocks

$modversion['blocks'][1]['file']		= "uhqradiobasic_blocks.php";
$modversion['blocks'][1]['name']		= _MI_UHQRADIOBASIC_BLOCK_STATUS_NAME;
$modversion['blocks'][1]['description']	= _MI_UHQRADIOBASIC_BLOCK_STATUS_DESC;
$modversion['blocks'][1]['show_func']	= "b_uhqradiobasic_status_show";
$modversion['blocks'][1]['edit_func']	= "b_uhqradiobasic_status_edit";
$modversion['blocks'][1]['template']	= "uhqradiobasic_status.html";
$modversion['blocks'][1]['options']		= "127.0.0.1|8000|I|showmestats|/incoming.ogg|0|/fallback.ogg|admin|0|0|{|0|}|0|http://localhost/tunein.html|_blank|200|300|0|0";

?>