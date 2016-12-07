<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2016 Eugen Mihailescu <eugenmihailescux@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * ################################################################################
 * 
 * Short description:
 * URL: http://wpmybackup.mynixworld.info
 * 
 * Git revision information:
 * 
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : locale.php $
 * 
 * @id      : locale.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once FUNCTIONS_PATH . 'utils.php';
! defined ( __NAMESPACE__.'\\LC_MESSAGES' ) && define ( __NAMESPACE__.'\\LC_MESSAGES', 5 );
! defined ( __NAMESPACE__.'\\LANG_PATH' ) && define ( __NAMESPACE__.'\\LANG_PATH', LOCALE_PATH . 'lang' . DIRECTORY_SEPARATOR );
function getAvailableLanguages() {
$lang_codes_file = LOCALE_PATH . 'lang-codes.txt';
if (_file_exists ( $lang_codes_file ) && false != preg_match_all ( '/\s*([\w\d_\.]+)*\s*,\s*(\w+)(,(\d*))*/u', preg_replace ( '/(^\s*#.*|^\s*$)/m', '', file_get_contents ( $lang_codes_file ) ), $matches )) {
$result = array_combine ( $matches [1], $matches [2] );
array_walk ( $result, function (&$item, $key) use(&$matches) {
$item = array (
$item => $matches [4] [array_search ( $item, $matches [2] )] 
);
} );
uasort ( $result, function ($a, $b) {
$a = end ( $a );
$b = end ( $b );
return - 1 * (empty ( $a ) ? 1 : (empty ( $b ) ? - 1 : ($a - $b)));
} );
return $result;
}
return array (
'' => array (
'English' => 100  // this is the default language translated 100%
) 
);
}
;
function getSelectedLangCode() {
$query = parse_url ( selfURL (), PHP_URL_QUERY );
$lang_code = preg_match ( '/[^?&]*lang=([^&]*)/', $query, $matches ) ? $matches [1] : false;
if (false === $lang_code && isset ( $_COOKIE ['cookie_accept'] ) && strToBool ( $_COOKIE ['cookie_accept'] ) && isset ( $_COOKIE ['lang'] ))
$lang_code = $_COOKIE ['lang'];
return $lang_code;
}
function setLanguage($lang_code = '') {
if (false === $lang_code)
return;
$lang_domain = 'default';
$lang_code = empty ( $lang_code ) ? getSelectedLangCode () : $lang_code;
$lang_locale = ! empty ( $lang_code ) ? $lang_code : '';
if (! empty ( $lang_locale )) {
if (isWin ())
$result = putenv ( "LC_ALL=$lang_locale" ); 
else
$result = setlocale ( LC_MESSAGES, $lang_locale );
$result || trigger_error ( sprintf ( 'Locale %s could not be set', $lang_locale ), E_USER_NOTICE );
bindtextdomain ( $lang_domain, LANG_PATH );
textdomain ( $lang_domain );
preg_match ( '/[^.]*\.(.*)/', $lang_locale, $matches ) && ($lang_codeset = $matches [1]) || $lang_codeset = 'utf8';
bind_textdomain_codeset ( $lang_domain, $lang_codeset );
}
}
?>