<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : WelcomeEditor.php $
 * 
 * @id      : WelcomeEditor.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
require_once CLASS_PATH . 'CheckSetup.php';
include_once EDITOR_PATH . 'file-functions.php';
class WelcomeEditor extends AbstractTargetEditor {
private $_init_error = false;
private $_addons;
private $_dropin_dir;
private $_video_ids;
private $_js_addon_install;
private $_nocheck;
private $_upload_constraint_manual;
private $_upload_constraint_link;
private function _checkPrerequisites() {
$chksetup = new CheckSetup( $this->settings );
$keys = array( CHKSETUP_ENABLED_KEY, CHKSETUP_ENABLED_SETTINGS, CHKSETUP_ENABLED_WRITABLE );
$setup_issues = array();
$array = $chksetup->getSetup();
foreach ( $array as $extension => $ext_info ) {
$ok = true;
foreach ( $keys as $key ) {
$ok = $ok && ( ! isset( $ext_info[$key] ) || strToBool( $ext_info[$key] ) );
}
if ( $ok || ( in_array( $extension, array( 'open_basedir', 'disable_functions', 'safe_mode' ) ) &&
! $ext_info[CHKSETUP_ENABLED_KEY] ) )
continue;
$setup_issues[$extension] = $ext_info;
}
return $setup_issues;
}
private function _initFileList( $filename ) {
if ( _file_exists( $filename ) )
return false;
$known_ext = array( 'php', 'css', 'js', 'png', 'jpg', 'gif', 'ico', 'pem', 'fix', 'txt', 'po', 'mo' );
$filelist = array();
$files = getFileListByPattern( ROOT_PATH, '/\.(' . implode( '|', $known_ext ) . ')$/i', true, false, false, 2 );
foreach ( $files as $file ) {
$filelist[str_replace( ROOT_PATH, '', $file )] = md5_file( $file );
}
return ! file_put_contents( $filename, json_encode( $filelist ) );
}
private function _getDropInAddons() {
if ( ! defined( __NAMESPACE__.'\\APP_ADDONDROPIN' ) )
return false;
$files = false;
if ( _is_dir( $this->_dropin_dir ) )
$files = getFileListByPattern( $this->_dropin_dir, '/\.tar\.bz2$/', true, false, false, 2 );
if ( empty( $files ) )
return false;
$addons = array();
$addonreg = new AddOnsRegister();
foreach ( $files as $filename ) {
if ( $addon = $addonreg->validate( $filename ) ) {
if ( ! isset( $addon['sku'] ) || $addonreg->isRegistered( $addon['sku'] ) )
continue;
$addons[] = basename( $filename );
}
}
return $addons;
}
private function _getJavaScripts() {
$this->java_scripts[] = 'parent.php_setup=function(){parent.asyncRunJob(parent.ajaxurl,"' . http_build_query( 
array( 'action' => 'php_setup', 'nonce' => wp_create_nonce_wrapper( 'php_setup' ) ) ) .
'","PHP Setup",null, null, 4, null, -1,null,false);}';
$this->java_scripts[] = 'parent.addon_action=function(action,nonce){document.getElementById(parent.globals.ADMIN_FORM).setAttribute("action",jsMyBackup.ajaxurl);document.getElementsByName("action")[0].value=action;document.getElementsByName("nonce")[0].value=nonce;};';
if ( ! ( $this->_init_error || empty( $this->_addons ) ) ) {
$this->java_scripts_load[] = $this->_js_addon_install;
}
}
protected function initTarget() {
global $TARGET_NAMES;
parent::initTarget();
$this->_upload_constraint_manual = PHP_MANUAL_URL . 'ini.core.php';
$this->_upload_constraint_link = array( 
getAnchor( 'upload_max_filesize', $this->_upload_constraint_manual . '#ini.upload-max-filesize' ), 
getAnchor( 'post_max_size', $this->_upload_constraint_manual . '#ini.post-max-size' ) );
$this->_js_addon_install = 'parent.addon_action("addon_install","' . wp_create_nonce_wrapper( 'addon_install' ) .
'");document.getElementById(parent.globals.ADMIN_FORM).submit();';
$this->hasCustomFrame = true;
if ( defined( __NAMESPACE__.'\\APP_ADDONDROPIN' ) ) {
$tmp_dir = addTrailingSlash( dirname(LOG_DIR));
$this->_dropin_dir = $tmp_dir . addTrailingSlash( $TARGET_NAMES[APP_ADDONDROPIN] );
$this->_addons = $this->_getDropInAddons();
} else
$this->_addons = array();
$this->_init_error = false;
empty( $this->_addons ) && $this->_init_error = $this->_initFileList( LOG_DIR . 'filelist.json' );
$this->_getJavaScripts();
$this->_video_ids = array( 'CmOLBfBRnrE' );
$this->_nocheck = isset( $_GET['nocheck'] );
}
protected function getEditorTemplate() {
global $registered_targets, $TARGET_NAMES, $COMPRESSION_NAMES;
$dropin_dir = shorten_path( $this->_dropin_dir ); 
$this->_nocheck || $setup_issues = $this->_checkPrerequisites();
is_wp() && $wp_components = array_map( function ( $item ) {
return basename( $item );
}, array_keys( getWPSourceDirList( WPMYBACKUP_ROOT ) ) );
! defined( __NAMESPACE__.'\\IMPORT_PAGE' ) && define( __NAMESPACE__.'\\IMPORT_PAGE', false );
$dashboard_link = getTabAnchorByConstant( 'APP_DASHBOARD' );
$_addons = $this->_addons;
$_nocheck = $this->_nocheck;
$getImgURL = function ( $filename ) {
return plugins_url_wrapper( 'img/' . $filename, IMG_PATH );
};
$getTabAnchor = function ( 
$tab, 
$query = null, 
$target = null, 
$escape = false, 
$array = null, 
$remove_query = null, 
$referer = false ) {
return getTabAnchor( $tab, $query, $target, $escape, $array, $remove_query, $referer );
};
$getTabAnchorByConstant = function ( $constant, $query = null, $target = null, $escape = false ) {
return getTabAnchorByConstant( $constant, $query, $target, $escape );
};
$getHumanReadableSize = function ( $size, $precision = 2, $return_what = 0 ) {
return getHumanReadableSize( $size, $precision, $return_what );
};
$getUploadLimit = function () {
return getUploadLimit();
};
$_init_error = $this->_init_error;
$_video_ids = $this->_video_ids;
$_upload_constraint_link = $this->_upload_constraint_link;
$is_wp = $this->is_wp;
$on_plugin = true;
require_once $this->getTemplatePath( 'welcome.php' );
}
}
?>