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
 * @file    : WelcomeEditor.php $
 * 
 * @id      : WelcomeEditor.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class WelcomeEditor extends AbstractTargetEditor {
private $_init_error = false;
private $_addons;
private $_dropin_dir;
private $_video_ids;
private $_js_addon_install;
private function _checkPrerequisites() {
$chksetup = new CheckSetup ( $this->settings );
$keys = array (
CHKSETUP_ENABLED_KEY,
CHKSETUP_ENABLED_SETTINGS,
CHKSETUP_ENABLED_WRITABLE 
);
$setup_issues = array ();
foreach ( $chksetup->getSetup () as $extension => $ext_info ) {
$ok = true;
foreach ( $keys as $key ) {
$ok = $ok && (! isset ( $ext_info [$key] ) || strToBool ( $ext_info [$key] ));
}
if ($ok || ('safe_mode' == $extension && ! $ext_info [CHKSETUP_ENABLED_KEY]))
continue;
$setup_issues [$extension] = $ext_info;
}
return $setup_issues;
}
private function _initFileList($filename) {
if (file_exists ( $filename ))
return false;
$known_ext = array (
'php',
'css',
'js',
'png',
'jpg',
'gif',
'ico',
'pem',
'fix',
'txt',
'po',
'mo' 
);
$filelist = array ();
$files = getFileListByPattern ( ROOT_PATH, '/\.(' . implode ( '|', $known_ext ) . ')$/i', true, false, false, 2 );
foreach ( $files as $file ) {
$filelist [str_replace ( ROOT_PATH, '', $file )] = md5_file ( $file );
}
return ! file_put_contents ( $filename, json_encode ( $filelist ) );
}
private function _getDropInAddons() {
if (! defined ( 'APP_ADDONDROPIN' ))
return false;
$files = false;
if (is_dir ( $this->_dropin_dir ))
$files = getFileListByPattern ( $this->_dropin_dir, '/\.tar\.bz2$/', true, false, false, 2 );
if (empty ( $files ))
return false;
$addons = array ();
$addonreg = new AddOnsRegister ();
foreach ( $files as $filename ) {
if ($addon = $addonreg->validate ( $filename )) {
if ($addonreg->isRegistered ( $addon ['sku'] ))
continue;
$addons [] = basename ( $filename );
}
}
return $addons;
}
private function _getJavaScripts() {
$this->java_scripts [] = 'parent.php_setup=function(){parent.asyncRunJob(parent.ajaxurl,"' . http_build_query ( array (
'action' => 'php_setup',
'nonce' => wp_create_nonce_wrapper ( 'php_setup' ) 
) ) . '","PHP Setup",null, null, 4, null, -1,null,false);}';
$this->java_scripts [] = 'parent.addon_action=function(action,nonce){document.getElementById(parent.globals.ADMIN_FORM).action=js55f93aab8f090.ajaxurl;document.getElementsByName("action")[0].value=action;document.getElementsByName("nonce")[0].value=nonce;};';
if (! ($this->_init_error || empty ( $this->_addons ))) {
$this->java_scripts_load [] = $this->_js_addon_install;
}
}
protected function initTarget() {
global $TARGET_NAMES;
parent::initTarget ();
$this->_js_addon_install = 'parent.addon_action("addon_install","' . wp_create_nonce_wrapper ( 'addon_install' ) . '");document.getElementById(parent.globals.ADMIN_FORM).submit();';
$this->hasCustomFrame = true;
if (defined ( 'APP_ADDONDROPIN' )) {
$this->_dropin_dir = ROOT_PATH . $TARGET_NAMES [APP_ADDONDROPIN] . DIRECTORY_SEPARATOR;
$this->_addons = $this->_getDropInAddons ();
} else
$this->_addons = array ();
$this->_init_error = false;
empty ( $this->_addons ) && $this->_init_error = $this->_initFileList ( LOG_DIR . 'filelist.json' );
$this->_getJavaScripts ();
$this->_video_ids = array (
'' 
);
}
protected function getEditorTemplate() {
global $registered_targets, $TARGET_NAMES;
$dropin_dir = str_replace ( ROOT_PATH, 'ROOT' . DIRECTORY_SEPARATOR, $this->_dropin_dir ); 
$setup_issues = $this->_checkPrerequisites ();
require_once $this->getTemplatePath ( 'welcome.php' );
}
}
?>
