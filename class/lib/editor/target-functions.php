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
 * @version : 0.2.3-8 $
 * @commit  : 010da912cb002abdf2f3ab5168bf8438b97133ea $
 * @author  : Eugen Mihailescu eugenmihailescux@gmail.com $
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : target-functions.php $
 * 
 * @id      : target-functions.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

function getTabTitleById( $target_type ) {
global $registered_targets;
if ( isset( $registered_targets[$target_type] ) && isset( $registered_targets[$target_type]['title'] ) )
return $registered_targets[$target_type]['title'];
return false;
}
function registerDefaultTab( $target_type, $class_name, $title, $file_function = null, $folder_style = null, $logo = null ) {
global $registered_targets;
registerTab( $target_type, $class_name, $title, $file_function, $folder_style, $logo );
foreach ( $registered_targets as $target_type => $array )
$registered_targets[$target_type]['default'] = false;
$registered_targets[$target_type]['default'] = true;
}
function registerTab( $target_type, $class_name, $title, $file_function = null, $folder_style = null, $logo = null ) {
global $registered_targets, $TARGET_NAMES, $settings;
$registered_targets[$target_type] = array( 
'default' => false, 
'class' => $class_name, 
'title' => $title, 
'file_function' => $file_function, 
'folder_style' => $folder_style, 
'logo' => $logo
);
}
function getTargetTypeByName( $name ) {
global $TARGET_NAMES;
$result = null;
foreach ( $TARGET_NAMES as $target_type => $target_name )
if ( $name == $target_name ) {
$result = $target_type;
break;
}
return $result;
}
function getTargetByName( $name ) {
global $registered_targets;
$target_type = getTargetTypeByName( $name );
return isset( $registered_targets[$target_type] ) ? $registered_targets[$target_type] : null;
}
function getTargetEditorClass( $target_type ) {
global $registered_targets;
return isset( $registered_targets[$target_type] ) ? $registered_targets[$target_type]['class'] : null;
}
function echoTargetEditor( $target_name ) {
global $TARGET_NAMES, $java_scripts, $java_scripts_load, $chart_script, $license, $license_id, $container_shape, $settings;
$err_msg = "<p style='color:red'>The tab you've mentioned '$target_name' is valid but its class %s</p>";
$err_msg .= "<p>This is somehow expected within the development version (nightly build).</p>";
$err_msg .= "<div class='hintbox {$container_shape}' style='display:inline-block'><b>Note</b>: it is not recommended to use this version in production.</div>";
$target_type = getTargetTypeByName( $target_name );
if ( null == ( $target_editor_class = getTargetEditorClass( $target_type ) ) ) {
echo sprintf( $err_msg, _esc( 'is not yet defined' ) );
return;
}
$target_editor_class = __NAMESPACE__ . '\\' . $target_editor_class;
$class_filename = EDITOR_PATH . preg_replace( '@(.*)(?<=[\\\\/])@', '', $target_editor_class ) . '.php';
if ( file_exists( $class_filename ) )
include_once $class_filename;
else {
echo sprintf( $err_msg, _esc( "file does not exist:" ) . "<br>" . $class_filename );
return;
}
if ( ! class_exists( $target_editor_class ) ) {
echo sprintf( 
$err_msg, 
sprintf( _esc( "is not yet defined as expected (ie named <b>%s</b>)" ), $target_editor_class ) );
return;
}
$target_info = getTargetByName( $target_name );
$item_def = array( 
'folder_style' => $target_info['folder_style'], 
'function_name' => $target_info['file_function'], 
'icon' => $target_info['logo'], 
'title' => $target_info['title'], 
'type' => $target_type, 
'targetSettings' => $settings );
$target_list = new TargetCollection();
$target_item = new TargetCollectionItem( $item_def );
$target_item = $target_list->addTargetItem( $target_item );
$editor = new $target_editor_class( $target_item );
$editor->showEditor();
$java_scripts = array_merge( $java_scripts, $editor->getJavaScripts() );
$java_scripts_load = array_merge( $java_scripts_load, $editor->getJavaScriptsLoad() );
$chart_script = array_merge( $chart_script, $editor->getJavaScriptsCharts() );
}
function getDefaultTab() {
global $registered_targets, $TARGET_NAMES;
foreach ( $registered_targets as $target_type => $array )
if ( $array['default'] )
return $TARGET_NAMES[$target_type];
return 'backup';
}
?>