<?php

define ('DS', DIRECTORY_SEPARATOR);

require_once '..' . DS . 'src' . DS . 'jLanguage' . DS . 'jLanguage.php';

$jLang = new jLanguage(array(
	// default language.
	'lang_default' => 'es',
	// select language from user browser language.
	'auto_location' => true
));

$jLang->set_section(array('index'));
?>

[[hello]]