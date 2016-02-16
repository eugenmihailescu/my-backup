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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : WelcomeEditor.php $
 * 
 * @id      : WelcomeEditor.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
include_once EDITOR_PATH . 'file-functions.php';
class WelcomeEditor extends AbstractTargetEditor {
private $_init_error = false;
private $_addons;
private $_dropin_dir;
private $_video_ids;
private $_js_addon_install;
private $_nocheck;
private $_upload_constraint_manual = 'http://php.net/manual/en/ini.core.php';
private $_upload_constraint_link;
private function _checkPrerequisites() {
$chksetup = new CheckSetup( $this->settings );
$keys = array( CHKSETUP_ENABLED_KEY, CHKSETUP_ENABLED_SETTINGS, CHKSETUP_ENABLED_WRITABLE );
$setup_issues = array();
foreach ( $chksetup->getSetup() as $extension => $ext_info ) {
$ok = true;
foreach ( $keys as $key ) {
$ok = $ok && ( ! isset( $ext_info[$key] ) || strToBool( $ext_info[$key] ) );
}
if ( $ok || ( 'safe_mode' == $extension && ! $ext_info[CHKSETUP_ENABLED_KEY] ) )
continue;
$setup_issues[$extension] = $ext_info;
}
return $setup_issues;
}
private function _initFileList( $filename ) {
if ( file_exists( $filename ) )
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
if ( is_dir( $this->_dropin_dir ) )
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
$this->_upload_constraint_link = array( 
getAnchor( 'upload_max_filesize', $this->_upload_constraint_manual . '#ini.upload-max-filesize' ), 
getAnchor( 'post_max_size', $this->_upload_constraint_manual . '#ini.post-max-size' ) );
$this->_js_addon_install = 'parent.addon_action("addon_install","' . wp_create_nonce_wrapper( 'addon_install' ) .
'");document.getElementById(parent.globals.ADMIN_FORM).submit();';
$this->hasCustomFrame = true;
if ( defined( __NAMESPACE__.'\\APP_ADDONDROPIN' ) ) {
$this->_dropin_dir = ROOT_PATH . $TARGET_NAMES[APP_ADDONDROPIN] . DIRECTORY_SEPARATOR;
$this->_addons = $this->_getDropInAddons();
} else
$this->_addons = array();
$this->_init_error = false;
empty( $this->_addons ) && $this->_init_error = $this->_initFileList( LOG_DIR . 'filelist.json' );
$this->_getJavaScripts();
$this->_video_ids = array( '' );
$this->_nocheck = isset( $_GET['nocheck'] );
}
protected function getEditorTemplate() {
global $registered_targets, $TARGET_NAMES, $COMPRESSION_NAMES;
$dropin_dir = str_replace( ROOT_PATH, 'ROOT' . DIRECTORY_SEPARATOR, $this->_dropin_dir ); 
$this->_nocheck || $setup_issues = $this->_checkPrerequisites();
is_wp() && $wp_components = array_map( function ( $item ) {
return basename( $item );
}, array_keys( getWPSourceDirList( WPMYBACKUP_ROOT ) ) );
$dashboard_link = getTabAnchorByConstant( 'APP_DASHBOARD' );
$restore_addon_link = getAnchor( _esc( 'Restore Addon' ), APP_ADDONS_SHOP_URI . 'shop/restore-wizard' );
$diff_restore_addon_link = getAnchor( 
_esc( 'Differential backup' ), 
APP_ADDONS_SHOP_URI . 'shop/differential-backup-support' );
$inc_restore_addon_link = getAnchor( 
_esc( 'Incremental backup' ), 
APP_ADDONS_SHOP_URI . 'shop/incremental-backup-support' );
$wpmybackup_plugin_link = getAnchor( WPMYBACKUP, 'https://wordpress.org/plugins/wp-mybackup' );
$arcname_pattern = '[a-z0-9\-\.]';
$gz_bz2_pre = '<pre>' . implode( '|', array( 'gz', 'bz2' ) ) . '</pre>';
! defined( __NAMESPACE__.'\\IMPORT_PAGE' ) && define( __NAMESPACE__.'\\IMPORT_PAGE', false );
require_once $this->getTemplatePath( 'welcome.php' );
}
}
?>