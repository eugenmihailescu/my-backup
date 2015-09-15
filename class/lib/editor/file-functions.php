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
 * @version : 0.2.0-10 $
 * @commit  : bc79573e2975a220cb1cfbb08b16615f721a68c5 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Sep 14 21:14:57 2015 +0200 $
 * @file    : file-functions.php $
 * 
 * @id      : file-functions.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

function getDiskFiles($path, $path_id = null, $filter, $directory_separator) {
$path = addTrailingSlash ( $path, $directory_separator, true );
$files = array ();
$dh = @opendir ( $path );
if ($dh) {
while ( false !== ($filename = @readdir ( $dh )) ) {
if ('.' == $filename || '..' == $filename)
continue;
$file = addTrailingSlash ( addslashes ( $path ), $directory_separator ) . $filename;
$is_dir = is_dir ( $file );
if (! $is_dir && ! empty ( $filter ) && 1 != preg_match ( "/\." . $filter . "$/", $filename ))
continue;
$files [] = array (
'name' => $file,
'is_dir' => $is_dir,
'size' => $is_dir ? 0 : @filesize ( $file ),
'time' => @filemtime ( $file ) 
);
}
}
return $files;
}
function getWebDAVFiles($path, $path_id = null, $filter, $directory_separator, $storage = null, $settings = null) {
$result = array ();
$metadata = $storage->metadata ( $path );
if (is_array ( $metadata )) {
foreach ( $metadata as $data ) {
if (! $data ['is_dir'] && ! empty ( $filter ) && 1 != preg_match ( "/\." . $filter . "$/", $data ['name'] ))
continue;
$result [] = $data;
}
} else {
$err = json_decode ( $metadata, true );
throw new MyException ( '<p style="color:red">' . $err ['message'] . '</p>', $err ['code'] );
}
return $result;
}
function getDropboxFiles($path, $path_id = null, $filter, $directory_separator, $storage) {
global $settings;
$direct_dwl = strToBool ( $settings ['dropbox_direct_dwl'] );
$files = array ();
try {
$metadata = $storage->metadata ( $path );
if (! (empty ( $metadata ) || empty ( $metadata ['contents'] ))) {
$raw_files = $metadata ['contents'];
foreach ( $raw_files as $file ) {
if (! $file ["is_dir"] && ! empty ( $filter ) && 1 != preg_match ( "/\." . $filter . "$/", $file ['path'] ))
continue;
$files [] = array (
'name' => $file ['path'],
'is_dir' => $file ["is_dir"],
'size' => $file ['bytes'],
'time' => strtotime ( $file ['modified'] ) 
);
if (! $file ["is_dir"] && $direct_dwl) {
$metadata = $storage->getDirectDownloadURL ( $file ['path'] );
if (! (empty ( $metadata ) || empty ( $metadata ['url'] )))
$files [count ( $files ) - 1] ['downloadUrl'] = $metadata ['url'];
}
}
}
} catch ( MyException $e ) {
}
return $files;
}
function getGoogleFiles($path, $path_id = null, $filter, $directory_separator, $storage = null) {
global $settings;
$direct_dwl = strToBool ( $settings ['google_direct_dwl'] );
$google_root = isnull ( $settings, 'google_root', 'root' );
if (empty ( $path ))
$path = $directory_separator;
if (empty ( $path_id ))
$path_id = $google_root;
$files = array ();
try {
if ($directory_separator == substr ( $path_id, - 1, strlen ( $directory_separator ) ))
$path_id = substr ( $path_id, 0, strlen ( $path_id ) - 1 );
$path_array = explode ( $directory_separator, $path_id );
if (count ( $path_array ) > 0)
$path_id = end ( $path_array );
$metadata = $storage->metadata ( $path_id );
if (! empty ( $metadata ) && ! empty ( $metadata ['items'] ) && is_array ( $metadata ['items'] )) {
$raw_files = $metadata ['items'];
if (count ( $raw_files ) > 0 && isset ( $raw_files [0] ['parents'] ) && count ( $raw_files [0] ['parents'] ) > 0)
$parent_id = $raw_files [0] ['parents'] [0] ['id'];
else
$parent_id = $google_root;
foreach ( $raw_files as $file ) {
$is_folder = strpos ( $file ["mimeType"], 'application/vnd.google-apps.folder' ) !== false;
if (! $is_folder && ! empty ( $filter ) && 1 != preg_match ( "/\." . $filter . "$/", $file ['title'] ))
continue;
$files [] = array (
'name' => $path . ($path != $directory_separator ? $directory_separator : '') . $file ['title'],
'is_dir' => $is_folder,
'size' => $is_folder ? 0 : $file ['quotaBytesUsed'],
'time' => strtotime ( $file ['createdDate'] ),
'file_id' => $file ['id'] ,
'iconLink' => $file ['iconLink'] ,
'parentId' => $parent_id 
);
if ($direct_dwl)
$files [count ( $files ) - 1] ['downloadUrl'] = stripUrlParams ( isset ( $file ['downloadUrl'] ) ? $file ['downloadUrl'] : $file ['alternateLink'], array (
'gd' 
) );
}
}
} catch ( MyException $e ) {
}
return $files;
}
function getFtpFiles($path, $path_id = null, $filter, $directory_separator, $storage = null, $settings = null, $is_sftp = false) {
$ftp = getFtpObject ( $settings, $is_sftp );
$result = array ();
$files = $ftp->getFtpFiles ( $path );
if (! is_array ( $files ))
return null;
foreach ( $files as $fname => $attr ) {
if (! ('.' == $fname || '..' == $fname || (! empty ( $filter ) && ! $attr [6] && ! preg_match ( "/\." . $filter . "$/", $fname ))))
$result [] = array (
'name' => $path . basename ( $fname ),
'is_dir' => $attr [6],
'size' => $attr [4],
'time' => $attr [5] 
);
}
return $result;
}
function getSSHFiles($path, $path_id = null, $filter, $directory_separator, $storage = null, $settings = null) {
return getFtpFiles ( $path, $path_id, $filter, $directory_separator, $storage, $settings, true );
}
function getChildrenDirSize(&$children) {
$size = 0;
foreach ( $children as $child )
if (isset ( $child ['size'] ))
$size += $child ['size'];
return $size;
}
function getDirList($path, $dir_show_size, $level = 2) {
$path_desc = null;
if (is_array ( $path )) {
$path_desc = $path ['desc'];
$path_style = $path ['style'];
$path_click = $path ['click'];
$path = key ( $path );
}
$path = addTrailingSlash ( $path );
$dh = @opendir ( $path );
$dirs = Array ();
if ($level > 0 && is_dir ( $path ) && $dh) {
while ( false !== ($filename = readdir ( $dh )) ) {
$fullPath = $path . $filename;
if ($filename != "." && $filename != "..")
if (is_dir ( $fullPath )) {
$children = getDirList ( $fullPath, $dir_show_size, $level - 1 );
ksort ( $children );
if ($dir_show_size) {
if (0 == count ( $children ))
$size = getDirSizeFromCache ( $fullPath );
else
$size = getChildrenDirSize ( $children );
} else
$size = null;
$dir = array (
'size' => $size 
);
! empty ( $path_desc ) && $dir ['desc'] = $path_desc;
! empty ( $path_style ) && $dir ['style'] = $path_style;
! empty ( $path_click ) && $dir ['click'] = $path_click;
if (is_array ( $children ) && count ( $children ) > 0)
$dir ['child'] = $children;
$dirs [$fullPath] = $dir;
}
}
}
return $dirs;
}
function getWPSourceDirList($path, $filters = null) {
if (! empty ( $filters ) && ! is_array ( $filters ))
$filters = array (
$filters 
);
$plugins_path = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins';
$theme_path = 'wp-content' . DIRECTORY_SEPARATOR . 'themes';
$wp_dirs = array (
'wp-admin' => array (
_esc ( 'WordPress Admin dashboard files' ),
_esc ( 'All PHP classes, CSS stylesheets, JavaScript files or images related to your WP Admin dashboard are stored here. Although they are part of the WP I would not say they represent the Core because without them your website will still work but no admin access, of course. Make sure you have a copy of these files too.' ) 
),
'wp-includes' => array (
_esc ( 'WordPress Core files' ),
_esc ( 'This is Zohan. You do not mess with Zohan! That is all I have to say!<br>PS: there would be something more to be said, though: ALWAYS have a copy of these files!' ) 
),
'wp-content' => array (
_esc ( 'Your uploaded data (ie. cache, imags, videos, files, etc)' ),
_esc ( 'When you write a post and embed some images/media/whatever on it they are not stored in the MySQL database but on the web server filesystem (ie. in this folder). The only part that is stored on the database is the text part. So you ALWAYS want to have a copy of these files too, unless you are happy with chunks of posts. I guess not!' ) 
),
$theme_path => array (
_esc ( 'WordPress installed Themes' ),
sprintf ( _esc ( 'This folder includes %d installed WordPress themes. It is always a good idea to backup these files.' ), getWPThemes ( true ) ) 
),
$plugins_path => array (
_esc ( 'WordPress installed Plugins' ),
sprintf ( _esc ( 'This folder includes %d installed WordPress plugins. It is always a good idea to backup these files.' ), count ( get_plugins () ) ) 
) 
);
if (is_array ( $filters ))
foreach ( array_keys ( $wp_dirs ) as $dir )
if (! in_array ( $dir, $filters ))
unset ( $wp_dirs [$dir] );
return $wp_dirs;
}
function getWPPluginsDirList($path, $group_style) {
$result = array ();
$filter = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins';
$desc = getWPSourceDirList ( $path, $filter );
$plugins_path = addTrailingSlash ( $path ) . $filter;
if (isset ( $desc [$filter] )) {
$dir_hint = '<b>' . $desc [$filter] [0] . '</b><br><blockquote>' . $desc [$filter] [1] . '</blockquote><b>Directory path</b> : ' . addslashes ( $plugins_path );
$result [$plugins_path] = array (
'desc' => $desc [$filter] [0],
'size' => 0,
'style' => $group_style,
'class' => 'help',
'click' => getHelpCall ( "'" . str_replace ( array (
"'",
'"' 
), array (
"\'",
'&quot;' 
), $dir_hint ) . "'", false ),
'child' => array () 
);
}
$t_size = 0;
$plugins = function_exists ( 'get_plugins' ) ? _call_user_func ( 'get_plugins' ) : array (); 
foreach ( $plugins as $plugin_relpath => $plugin_info ) {
$plugin_abspath = addTrailingSlash ( $plugins_path ) . (false === strpos ( $plugin_relpath, DIRECTORY_SEPARATOR ) ? '' : dirname ( $plugin_relpath ));
$plugin_abspath = addTrailingSlash ( $plugin_abspath );
$size = false === strpos ( $plugin_abspath, dirname ( dirname ( plugin_dir_path ( __FILE__ ) ) ) ) ? getDirSizeFromCache ( $plugin_abspath ) : 0;
$t_size += $size;
$result [$plugins_path] ['child'] [$plugin_abspath] = array (
'size' => $size,
'desc' => $plugin_info ['Title'],
'class' => 'help',
'click' => getHelpCall ( "'" . str_replace ( array (
"'",
'"' 
), array (
"\'",
'&quot;' 
), $plugin_info ['Description'] ) . "'", false ) 
);
}
$result [$plugins_path] ['size'] = $t_size;
return $result;
}
function getWPThemesDirList($path, $group_style) {
$result = array ();
$filter = 'wp-content' . DIRECTORY_SEPARATOR . 'themes';
$desc = getWPSourceDirList ( $path, $filter );
$theme_path = addTrailingSlash ( $path ) . $filter;
if (isset ( $desc [$filter] )) {
$dir_hint = '<b>' . $desc [$filter] [0] . '</b><br><blockquote>' . $desc [$filter] [1] . '</blockquote><b>Directory path</b> : ' . addslashes ( $theme_path );
$result [$theme_path] = array (
'desc' => $desc [$filter] [0],
'size' => 0,
'style' => $group_style,
'class' => 'help',
'click' => getHelpCall ( "'" . str_replace ( array (
"'",
'"' 
), array (
"\'",
'&quot;' 
), $dir_hint ) . "'", false ),
'child' => array () 
);
}
$t_size = 0;
$themes = getWPThemes (); 
foreach ( $themes as $theme_name => $theme_obj ) {
$theme_abspath = (is_object ( $theme_obj ) ? $theme_obj->__get ( 'stylesheet_dir' ) : $theme_obj ['Stylesheet Dir']) . DIRECTORY_SEPARATOR;
$theme_abspath = str_replace ( '/', DIRECTORY_SEPARATOR, $theme_abspath );
$theme_desc = isset ( $theme_obj ['Description'] ) ? $theme_obj ['Description'] : $theme_obj->__get ( 'description' );
$size = getDirSizeFromCache ( $theme_abspath );
$t_size += $size;
$result [$theme_path] ['child'] [$theme_abspath] = array (
'size' => $size,
'desc' => $theme_name,
'class' => 'help',
'click' => getHelpCall ( "'" . str_replace ( array (
"'",
'"' 
), array (
"\'",
'&quot;' 
), $theme_desc ) . "'", false ) 
);
}
$result [$theme_path] ['size'] = $t_size;
return $result;
}
function getWPDirList($path, $dir_show_size, $level = 2) {
$level = 1;
$result = array ();
$wp_dirs = getWPSourceDirList ( $path, array (
'wp-admin',
'wp-includes',
'wp-content' 
) );
$group_style = array (
'row' => 'background-color:#00adee;color:white',
'link' => 'color:white' 
);
foreach ( $wp_dirs as $dir => $desc ) {
$dirname = $path . $dir;
$item = getDirList ( $dirname, $dir_show_size, $level );
foreach ( $item as $key => $value )
if (! isset ( $value ['desc'] ))
$item [$key] ['click'] = ' ';
if ('wp-content' == $dir) {
unset ( $item [$dirname . '/plugins'] );
unset ( $item [$dirname . '/themes'] );
}
if (! empty ( $item )) {
$dir_hint = '<b>' . $desc [0] . '</b><br><blockquote>' . $desc [1] . '</blockquote><b>Directory path</b> : ' . addslashes ( $dirname );
$result [$dirname] = array (
'desc' => $desc [0],
'size' => array_reduce ( $item, function ($carry, $el) {
$carry += $el ['size'];
return $carry;
} ),
'style' => $group_style,
'class' => 'help',
'click' => getHelpCall ( "'" . str_replace ( array (
"'",
'"' 
), array (
"\'",
'&quot;' 
), $dir_hint ) . "'", false ) 
) + $item;
$result [$dirname] ['child'] = $item;
}
}
$result += getWPPluginsDirList ( $path, $group_style );
$result += getWPThemesDirList ( $path, $group_style );
return $result;
}
function getWPThemes($count = false) {
$func_name = version_compare ( get_bloginfo ( 'version', 'display' ), '3.4', '<' ) ? 'get_themes' : 'wp_get_themes';
$result = function_exists ( $func_name ) ? _call_user_func ( $func_name ) : array ();
return $count ? count ( $result ) : $result;
}
?>
