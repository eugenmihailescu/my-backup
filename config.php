<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2015 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : config.php $
 * 
 * @id      : config.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

define ( "JS_NAMESPACE" , "js55f93aab8f090" );
define('ROOT_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('TMP_PATH',ROOT_PATH.'tmp'.DIRECTORY_SEPARATOR);
define('LOGS_PATH',TMP_PATH.'logs'.DIRECTORY_SEPARATOR);
define('INC_PATH',ROOT_PATH.'inc'.DIRECTORY_SEPARATOR);
define('CLASS_PATH',ROOT_PATH.'class'.DIRECTORY_SEPARATOR);
define('FUNCTIONS_PATH',CLASS_PATH.'functions'.DIRECTORY_SEPARATOR);
define('ADDONFUNC_PATH',FUNCTIONS_PATH.'addonfunc'.DIRECTORY_SEPARATOR);
define('UTILS_PATH',FUNCTIONS_PATH.'utils'.DIRECTORY_SEPARATOR);
define('LIB_PATH',CLASS_PATH.'lib'.DIRECTORY_SEPARATOR);
define('CURL_PATH',LIB_PATH.'curl'.DIRECTORY_SEPARATOR);
define('EDITOR_PATH',LIB_PATH.'editor'.DIRECTORY_SEPARATOR);
define('TEMPLATES_PATH',EDITOR_PATH.'templates'.DIRECTORY_SEPARATOR);
define('MISC_PATH',LIB_PATH.'misc'.DIRECTORY_SEPARATOR);
define('OAUTH_PATH',LIB_PATH.'oauth'.DIRECTORY_SEPARATOR);
define('STORAGE_PATH',LIB_PATH.'storage'.DIRECTORY_SEPARATOR);
define('VIEWER_PATH',LIB_PATH.'viewer'.DIRECTORY_SEPARATOR);
define('CONFIG_PATH',ROOT_PATH.'config'.DIRECTORY_SEPARATOR);
define('ADDONS_PATH',CONFIG_PATH.'addons'.DIRECTORY_SEPARATOR);
define('RULES_PATH',CONFIG_PATH.'rules'.DIRECTORY_SEPARATOR);
define('CSS_PATH',ROOT_PATH.'css'.DIRECTORY_SEPARATOR);
define('IMG_PATH',ROOT_PATH.'img'.DIRECTORY_SEPARATOR);
define('JS_PATH',ROOT_PATH.'js'.DIRECTORY_SEPARATOR);
define('LOCALE_PATH',ROOT_PATH.'locale'.DIRECTORY_SEPARATOR);
define('CUSTOM_PATH',ROOT_PATH.'custom'.DIRECTORY_SEPARATOR);
define('SAMPLE_PATH',CUSTOM_PATH.'sample'.DIRECTORY_SEPARATOR);
define('SSL_PATH',ROOT_PATH.'ssl'.DIRECTORY_SEPARATOR);
defined('CONFIG_PATH') && ($c=CONFIG_PATH.'config-custom.php') && file_exists($c) && (include_once $c) || define ('MYBACKUP_CONFIG_PATH_NOT_FOUND', 'CONFIG_PATH not defined. Your installation seems to be corupted.');
?>
