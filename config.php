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
 * @version : 0.2.3-27 $
 * @commit  : 10d36477364718fdc9b9947e937be6078051e450 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 10:06:27 2016 +0100 $
 * @file    : config.php $
 * 
 * @id      : config.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

!defined(__NAMESPACE__."\\JS_NAMESPACE") && define ( __NAMESPACE__."\\JS_NAMESPACE" , "jsMyBackup" );
!defined(__NAMESPACE__.'\\ROOT_PATH') && define(__NAMESPACE__.'\\ROOT_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\TMP_PATH') && define(__NAMESPACE__.'\\TMP_PATH',ROOT_PATH.'tmp'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\LOGS_PATH') && define(__NAMESPACE__.'\\LOGS_PATH',TMP_PATH.'logs'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\INC_PATH') && define(__NAMESPACE__.'\\INC_PATH',ROOT_PATH.'inc'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\CLASS_PATH') && define(__NAMESPACE__.'\\CLASS_PATH',ROOT_PATH.'class'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\FUNCTIONS_PATH') && define(__NAMESPACE__.'\\FUNCTIONS_PATH',CLASS_PATH.'functions'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\ADDONFUNC_PATH') && define(__NAMESPACE__.'\\ADDONFUNC_PATH',FUNCTIONS_PATH.'addonfunc'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\UTILS_PATH') && define(__NAMESPACE__.'\\UTILS_PATH',FUNCTIONS_PATH.'utils'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\LIB_PATH') && define(__NAMESPACE__.'\\LIB_PATH',CLASS_PATH.'lib'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\CURL_PATH') && define(__NAMESPACE__.'\\CURL_PATH',LIB_PATH.'curl'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\EDITOR_PATH') && define(__NAMESPACE__.'\\EDITOR_PATH',LIB_PATH.'editor'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\TEMPLATES_PATH') && define(__NAMESPACE__.'\\TEMPLATES_PATH',EDITOR_PATH.'templates'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\MISC_PATH') && define(__NAMESPACE__.'\\MISC_PATH',LIB_PATH.'misc'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\OAUTH_PATH') && define(__NAMESPACE__.'\\OAUTH_PATH',LIB_PATH.'oauth'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\STORAGE_PATH') && define(__NAMESPACE__.'\\STORAGE_PATH',LIB_PATH.'storage'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\VIEWER_PATH') && define(__NAMESPACE__.'\\VIEWER_PATH',LIB_PATH.'viewer'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\CONFIG_PATH') && define(__NAMESPACE__.'\\CONFIG_PATH',ROOT_PATH.'config'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\ADDONS_PATH') && define(__NAMESPACE__.'\\ADDONS_PATH',CONFIG_PATH.'addons'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\RULES_PATH') && define(__NAMESPACE__.'\\RULES_PATH',CONFIG_PATH.'rules'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\CSS_PATH') && define(__NAMESPACE__.'\\CSS_PATH',ROOT_PATH.'css'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\CUSTOM_PATH') && define(__NAMESPACE__.'\\CUSTOM_PATH',ROOT_PATH.'custom'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\SAMPLE_PATH') && define(__NAMESPACE__.'\\SAMPLE_PATH',CUSTOM_PATH.'sample'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\IMG_PATH') && define(__NAMESPACE__.'\\IMG_PATH',ROOT_PATH.'img'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\JS_PATH') && define(__NAMESPACE__.'\\JS_PATH',ROOT_PATH.'js'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\LOCALE_PATH') && define(__NAMESPACE__.'\\LOCALE_PATH',ROOT_PATH.'locale'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\SSL_PATH') && define(__NAMESPACE__.'\\SSL_PATH',ROOT_PATH.'ssl'.DIRECTORY_SEPARATOR);
!defined(__NAMESPACE__.'\\APP_SLUG') && define(__NAMESPACE__.'\\APP_SLUG','mybackup');
defined(__NAMESPACE__.'\\CONFIG_PATH') && ($c=CONFIG_PATH.'config-custom.php') && file_exists($c) && (include_once $c) || define (__NAMESPACE__.'\\MYBACKUP_CONFIG_PATH_NOT_FOUND', 'CONFIG_PATH not defined. Your installation seems to be corupted.');
?>