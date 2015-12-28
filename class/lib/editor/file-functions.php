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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : file-functions.php $
 * 
 * @id      : file-functions.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function getDiskFiles( $path, $path_id = null, $filter, $directory_separator ) {
$path = addTrailingSlash( $path, $directory_separator, true );
$files = array();
$dh = @opendir( $path );
if ( $dh ) {
while ( false !== ( $filename = @readdir( $dh ) ) ) {
if ( '.' == $filename || '..' == $filename )
continue;
$file = addTrailingSlash( addslashes( $path ), $directory_separator ) . $filename;
$is_dir = is_dir( $file );
if ( ! $is_dir && ! empty( $filter ) && 1 != preg_match( "/\." . $filter . "$/", $filename ) )
continue;
$files[] = array( 
'name' => $file, 
'is_dir' => $is_dir, 
'size' => $is_dir ? 0 : @filesize( $file ), 
'time' => @filemtime( $file ) );
}
}
return $files;
}
function getWebDAVFiles( $path, $path_id = null, $filter, $directory_separator, $storage = null, $settings = null ) {
$result = array();
$metadata = $storage->metadata( $path );
if ( is_array( $metadata ) ) {
foreach ( $metadata as $data ) {
if ( ! $data['is_dir'] && ! empty( $filter ) && 1 != preg_match( "/\." . $filter . "$/", $data['name'] ) )
continue;
$result[] = $data;
}
} else {
$err = json_decode( $metadata, true );
throw new MyException( '<p style="color:red">' . $err['message'] . '</p>', $err['code'] );
}
return $result;
}
function getDropboxFiles( $path, $path_id = null, $filter, $directory_separator, $storage ) {
global $settings;
$direct_dwl = strToBool( $settings['dropbox_direct_dwl'] );
$files = array();
try {
$metadata = $storage->metadata( $path );
if ( ! ( empty( $metadata ) || empty( $metadata['contents'] ) ) ) {
$raw_files = $metadata['contents'];
foreach ( $raw_files as $file ) {
if ( ! $file["is_dir"] && ! empty( $filter ) && 1 != preg_match( "/\." . $filter . "$/", $file['path'] ) )
continue;
$files[] = array( 
'name' => $file['path'], 
'is_dir' => $file["is_dir"], 
'size' => $file['bytes'], 
'time' => strtotime( $file['modified'] ) );
if ( ! $file["is_dir"] && $direct_dwl ) {
$metadata = $storage->getDirectDownloadURL( $file['path'] );
if ( ! ( empty( $metadata ) || empty( $metadata['url'] ) ) )
$files[count( $files ) - 1]['downloadUrl'] = $metadata['url'];
}
}
}
} catch ( MyException $e ) {
}
return $files;
}
function getGoogleFiles( $path, $path_id = null, $filter, $directory_separator, $storage = null ) {
global $settings;
$direct_dwl = strToBool( $settings['google_direct_dwl'] );
$google_root = isnull( $settings, 'google_root', 'root' );
if ( empty( $path ) )
$path = $directory_separator;
if ( empty( $path_id ) )
$path_id = $google_root;
$files = array();
try {
if ( $directory_separator == substr( $path_id, - 1, strlen( $directory_separator ) ) )
$path_id = substr( $path_id, 0, strlen( $path_id ) - 1 );
$path_array = explode( $directory_separator, $path_id );
if ( count( $path_array ) > 0 )
$path_id = end( $path_array );
$metadata = $storage->metadata( $path_id );
if ( ! empty( $metadata ) && ! empty( $metadata['items'] ) && is_array( $metadata['items'] ) ) {
$raw_files = $metadata['items'];
if ( count( $raw_files ) > 0 && isset( $raw_files[0]['parents'] ) && count( $raw_files[0]['parents'] ) > 0 )
$parent_id = $raw_files[0]['parents'][0]['id'];
else
$parent_id = $google_root;
foreach ( $raw_files as $file ) {
$is_folder = strpos( $file["mimeType"], 'application/vnd.google-apps.folder' ) !== false;
if ( ! $is_folder && ! empty( $filter ) && 1 != preg_match( "/\." . $filter . "$/", $file['title'] ) )
continue;
$files[] = array( 
'name' => $path . ( $path != $directory_separator ? $directory_separator : '' ) . $file['title'], 
'is_dir' => $is_folder, 
'size' => $is_folder ? 0 : $file['quotaBytesUsed'], 
'time' => strtotime( $file['createdDate'] ), 
'file_id' => $file['id'] ,
'iconLink' => $file['iconLink'] ,
'parentId' => $parent_id );
if ( $direct_dwl )
$files[count( $files ) - 1]['downloadUrl'] = stripUrlParams( 
isset( $file['downloadUrl'] ) ? $file['downloadUrl'] : $file['alternateLink'], 
array( 'gd' ) );
}
}
} catch ( MyException $e ) {
}
return $files;
}
function getFtpFiles( $path, $path_id = null, $filter, $directory_separator, $storage = null, $settings = null, $is_sftp = false ) {
$ftp = getFtpObject( $settings, $is_sftp );
$result = array();
$files = $ftp->getFtpFiles( $path );
if ( ! is_array( $files ) )
return null;
foreach ( $files as $fname => $attr ) {
if ( ! ( '.' == $fname || '..' == $fname ||
( ! empty( $filter ) && ! $attr[6] && ! preg_match( "/\." . $filter . "$/", $fname ) ) ) )
$result[] = array( 
'name' => $path . basename( $fname ), 
'is_dir' => $attr[6], 
'size' => $attr[4], 
'time' => $attr[5] );
}
return $result;
}
function getSSHFiles( $path, $path_id = null, $filter, $directory_separator, $storage = null, $settings = null ) {
return getFtpFiles( $path, $path_id, $filter, $directory_separator, $storage, $settings, true );
}
function getChildrenDirSize( &$children ) {
$size = 0;
foreach ( $children as $child )
if ( isset( $child['size'] ) )
$size += $child['size'];
return $size;
}
function getDirList( $path, $dir_show_size, $level = 2, $output_style = true ) {
$path_desc = null;
if ( is_array( $path ) ) {
$path_desc = $path['desc'];
$path_style = $path['style'];
$path_click = $path['click'];
$path = key( $path );
}
$path = addTrailingSlash( $path );
$dh = @opendir( $path );
$dirs = array();
if ( $level > 0 && is_dir( $path ) && $dh ) {
while ( false !== ( $filename = readdir( $dh ) ) ) {
$fullPath = $path . $filename;
if ( $filename != "." && $filename != ".." )
if ( is_dir( $fullPath ) ) {
$children = getDirList( $fullPath, $dir_show_size, $level - 1, $output_style );
$output_style && ksort( $children ) || asort( $children );
if ( $output_style ) {
if ( $dir_show_size ) {
if ( 0 == count( $children ) )
$size = getDirSizeFromCache( $fullPath );
else
$size = getChildrenDirSize( $children );
} else
$size = null;
$dir = array( 'size' => $size );
! empty( $path_desc ) && $dir['desc'] = $path_desc;
! empty( $path_style ) && $dir['style'] = $path_style;
! empty( $path_click ) && $dir['click'] = $path_click;
}
if ( is_array( $children ) && count( $children ) > 0 ) {
if ( $output_style )
$dir['child'] = $children;
else
$dirs = array_merge( $dirs, $children );
}
if ( $output_style )
$dirs[$fullPath] = $dir;
else
$dirs[] = $fullPath;
}
}
}
return $dirs;
}
function getWPSourceDirList( $path, $filters = null, $reverse_filter = false ) {
if ( ! empty( $filters ) && ! is_array( $filters ) )
$filters = array( $filters );
$wp_dirs = array();
$is_multisite = is_multisite_wrapper();
$wp_upload_dir = wp_get_upload_dir();
$WP_CONTENT_DIR = @constant( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ( $path . 'wp-content' );
$WPINC = @constant( 'WPINC' ) ? WPINC : 'wp-includes';
$wp_content = basename( $WP_CONTENT_DIR );
$plugins_rel_path = @constant( 'PLUGINDIR' ) ? str_replace( dirname( $WP_CONTENT_DIR ), '', PLUGINDIR ) : $wp_content .
DIRECTORY_SEPARATOR . 'plugins';
$theme_rel_path = $wp_content . DIRECTORY_SEPARATOR . 'themes';
if ( is_administrator() ) {
$key = $is_multisite ? str_replace( 
dirname( $WP_CONTENT_DIR ) . DIRECTORY_SEPARATOR, 
'', 
$wp_upload_dir['basedir'] ) : $wp_content;
$wp_dirs = $wp_dirs +
array( 
$key => array( 
_esc( 'Your uploaded data (ie. cache, imags, videos, files, etc)' ), 
_esc( 
'When you write a post and embed some images/media/whatever on it they are not stored in the MySQL database but on the web server filesystem (ie. in this folder). The only part that is stored on the database is the text part. So you ALWAYS want to have a copy of these files too, unless you are happy with chunks of posts. I guess not!' ) ) );
}
$wp_dirs = $wp_dirs + array( 
$theme_rel_path => array( 
_esc( 'WordPress installed Themes' ), 
sprintf( 
_esc( 
'This folder includes %d installed WordPress themes. It is always a good idea to backup these files.' ), 
getWPThemes( true ) ) ), 
$plugins_rel_path => array( 
_esc( 'WordPress installed Plugins' ), 
sprintf( 
_esc( 
'This folder includes %d installed WordPress plugins. It is always a good idea to backup these files.' ), 
count( wp_get_plugins( \current_user_can( 'activate_plugins' ) ? 'all' : 'active' ) ) ) ) );
( ! ( $is_multisite && is_administrator() ) || is_wpmu_superadmin() ) &&
$wp_dirs = $wp_dirs +
array( 
'wp-admin' => array( 
_esc( 'WordPress Admin dashboard files' ), 
_esc( 
'All PHP classes, CSS stylesheets, JavaScript files or images related to your WP Admin dashboard are stored here. Although they are part of the WP I would not say they represent the Core because without them your website will still work but no admin access, of course. Make sure you have a copy of these files too.' ) ), 
basename( $WPINC ) => array( 
_esc( 'WordPress Core files' ), 
_esc( 
'This is Zohan. You do not mess with Zohan! That is all I have to say!<br>PS: there would be something more to be said, though: ALWAYS have a copy of these files!' ) ) );
if ( ! empty( $filters ) ) {
in_array( $wp_content, $filters ) &&
$filters = array_merge( $filters, array( $plugins_rel_path, $theme_rel_path ) );
foreach ( array_keys( $wp_dirs ) as $dir ) {
$exists = in_array( $dir, $filters );
if ( ! ( $reverse_filter || $exists ) || ( $reverse_filter && $exists ) )
unset( $wp_dirs[$dir] );
}
}
return $wp_dirs;
}
function getWPPluginsDirList( $path, $group_style, $output_style = true ) {
$result = array();
$filter = 'wp-content' . DIRECTORY_SEPARATOR . 'plugins';
$desc = getWPSourceDirList( $path, $filter );
$plugins_path = addTrailingSlash( $path ) . $filter;
if ( isset( $desc[$filter] ) ) {
if ( $output_style ) {
$dir_hint = '<b>' . $desc[$filter][0] . '</b><br><blockquote>' . $desc[$filter][1] .
'</blockquote><b>Directory path</b> : ' . addslashes( $plugins_path );
$result[$plugins_path] = array( 
'desc' => $desc[$filter][0], 
'size' => 0, 
'style' => $group_style, 
'class' => 'help', 
'click' => getHelpCall( 
"'" . str_replace( array( "'", '"' ), array( "\'", '&quot;' ), $dir_hint ) . "'", 
false ), 
'child' => array() );
}
}
$t_size = 0;
$plugins = wp_get_plugins(\current_user_can( 'activate_plugins' ) ? 'all' : 'active' );
foreach ( $plugins as $plugin_relpath => $plugin_info ) {
$plugin_abspath = addTrailingSlash( $plugins_path ) .
( false === strpos( $plugin_relpath, DIRECTORY_SEPARATOR ) ? '' : dirname( $plugin_relpath ) );
$plugin_abspath = addTrailingSlash( $plugin_abspath );
if ( $output_style ) {
$size = false === strpos( $plugin_abspath, dirname( dirname( plugin_dir_path( __FILE__ ) ) ) ) ? getDirSizeFromCache( 
$plugin_abspath ) : 0;
$t_size += $size;
$result[$plugins_path]['child'][$plugin_abspath] = array( 
'size' => $size, 
'desc' => $plugin_info['Title'], 
'class' => 'help', 
'click' => getHelpCall( 
"'" . str_replace( array( "'", '"' ), array( "\'", '&quot;' ), $plugin_info['Description'] ) . "'", 
false ) );
} else
( $plugins_path != $plugin_abspath ) && $result[] = delTrailingSlash( $plugin_abspath );
}
$output_style && $result[$plugins_path]['size'] = $t_size;
return $result;
}
function getWPThemesDirList( $path, $group_style, $output_style = true ) {
$result = array();
$filter = 'wp-content' . DIRECTORY_SEPARATOR . 'themes';
$desc = getWPSourceDirList( $path, $filter );
$theme_path = addTrailingSlash( $path ) . $filter;
if ( isset( $desc[$filter] ) ) {
if ( $output_style ) {
$dir_hint = '<b>' . $desc[$filter][0] . '</b><br><blockquote>' . $desc[$filter][1] .
'</blockquote><b>Directory path</b> : ' . addslashes( $theme_path );
$result[$theme_path] = array( 
'desc' => $desc[$filter][0], 
'size' => 0, 
'style' => $group_style, 
'class' => 'help', 
'click' => getHelpCall( 
"'" . str_replace( array( "'", '"' ), array( "\'", '&quot;' ), $dir_hint ) . "'", 
false ), 
'child' => array() );
}
}
$t_size = 0;
$themes = getWPThemes(); 
foreach ( $themes as $theme_name => $theme_obj ) {
$theme_abspath = ( is_object( $theme_obj ) ? $theme_obj->__get( 'stylesheet_dir' ) : $theme_obj['Stylesheet Dir'] ) .
DIRECTORY_SEPARATOR;
$theme_abspath = str_replace( '/', DIRECTORY_SEPARATOR, $theme_abspath );
if ( $output_style ) {
$theme_desc = isset( $theme_obj['Description'] ) ? $theme_obj['Description'] : $theme_obj->__get( 
'description' );
$size = getDirSizeFromCache( $theme_abspath );
$t_size += $size;
$result[$theme_path]['child'][$theme_abspath] = array( 
'size' => $size, 
'desc' => $theme_name, 
'class' => 'help', 
'click' => getHelpCall( 
"'" . str_replace( array( "'", '"' ), array( "\'", '&quot;' ), $theme_desc ) . "'", 
false ) );
} else
( $theme_path != $theme_abspath ) && $result[] = $theme_abspath;
}
$output_style && $result[$theme_path]['size'] = $t_size;
return $result;
}
function getWPDirList( $path, $dir_show_size = false, $level = 2, $output_style = true ) {
$unset_array = function ( &$array, $key, $by_value = false ) {
$by_value && $key = array_search( $key, $array );
if ( isset( $array[$key] ) )
unset( $array[$key] );
};
$array_has_sufix = function ( $array, $sufix, $by_value = false ) {
! $by_value && $array = array_keys( $array );
$has_sufix = false;
foreach ( $array as $key )
if ( preg_match( '/' . preg_quote( $sufix, '/' ) . '$/', $key ) && ( $has_sufix = $key ) )
break;
return $has_sufix ? ( $by_value ? array_search( $has_sufix, $array ) : $has_sufix ) : false;
};
$level = 1;
$result = array();
$path = addTrailingSlash( $path );
$group_style = array( 'row' => 'background-color:#00adee;color:white', 'link' => 'color:white' );
$multisite = is_multisite_wrapper();
$wp_upload_dir = wp_get_upload_dir();
$WP_CONTENT_DIR = @constant( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ( $path . 'wp-content' );
$WP_PLUGIN_DIR = @constant( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : ( $WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' );
$WP_THEMES_DIR = function_exists( '\\get_theme_root' ) ? get_theme_root() : ( $WP_CONTENT_DIR . DIRECTORY_SEPARATOR .
'themes' );
$WP_UPLOADS_DIR = is_array( $wp_upload_dir ) && isset( $wp_upload_dir['basedir'] ) ? $wp_upload_dir['basedir'] : ( $WP_CONTENT_DIR .
DIRECTORY_SEPARATOR . 'uploads' );
$wp_dir_filter = array();
$multisite && $wp_dir_filter = array( basename( $WP_CONTENT_DIR ) );
$wp_dir = getWPSourceDirList( $path, $wp_dir_filter, $multisite );
$wp_content = 'wp-content';
$wp_plugins = $wp_content . DIRECTORY_SEPARATOR . 'plugins';
$wp_themes = $wp_content . DIRECTORY_SEPARATOR . 'themes';
foreach ( $wp_dir as $dir => $desc ) {
if ( in_array( $dir, array( $wp_plugins, $wp_themes ) ) ) {
$wp_plugins == $dir && $items = getWPPluginsDirList( $path, $group_style, $output_style );
$wp_themes == $dir && $items = getWPThemesDirList( $path, $group_style, $output_style );
$output_style && ( $result += $items ) || $result = array_merge( $result, $items );
continue;
}
$dirname = $path . $dir;
$item = getDirList( $dirname, $dir_show_size, $level, $output_style );
if ( $wp_content == $dir ) {
( $key = $array_has_sufix( $item, $wp_plugins, ! $output_style ) ) && $unset_array( $item, $key );
( $key = $array_has_sufix( $item, $wp_themes, ! $output_style ) ) && $unset_array( $item, $key );
}
if ( $output_style ) {
foreach ( $item as $key => $value )
! isset( $value['desc'] ) && $item[$key]['click'] = ' ';
}
if ( ! empty( $item ) ) {
if ( $output_style ) {
$dir_hint = '<b>' . $desc[0] . '</b><br><blockquote>' . $desc[1] .
'</blockquote><b>Directory path</b> : ' . addslashes( $dirname );
$result[$dirname] = array( 
'desc' => $desc[0], 
'size' => array_reduce( 
$item, 
function ( $carry, $el ) {
$carry += $el['size'];
return $carry;
} ), 
'style' => $group_style, 
'class' => 'help', 
'click' => getHelpCall( 
"'" . str_replace( array( "'", '"' ), array( "\'", '&quot;' ), $dir_hint ) . "'", 
false ) ) + $item;
} else {
$result[] = $dirname;
$result = array_merge( $result, $item );
}
$output_style && $result[$dirname]['child'] = $item;
}
}
if ( $multisite ) {
$plugins = getWPPluginsDirList( $path, $group_style, $output_style );
$themes = getWPThemesDirList( $path, $group_style, $output_style );
$output_style && ( $result += $plugins + $themes ) || $result = array_merge( $result, $plugins, $themes );
}
if ( ! $output_style & ( ( $multisite && is_wpmu_superadmin() ) || ( ! $multisite && is_administrator() ) ) )
array_unshift( $result, $path );
return $result;
}
function getWPThemes( $count = false ) {
$func_name = version_compare( get_bloginfo( 'version', 'display' ), '3.4', '<' ) ? 'get_themes' : 'wp_get_themes';
$result = _function_exists( $func_name ) ? wp_exec_in_blog( 
$func_name, 
array( array( 'allowed' => true, 'blog_id' => wp_get_current_blog_id() ) ) ) : array();
return $count ? count( $result ) : $result;
}
?>