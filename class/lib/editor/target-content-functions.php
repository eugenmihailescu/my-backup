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
 * @file    : target-content-functions.php $
 * 
 * @id      : target-content-functions.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once 'file-functions.php';
function echoFileListContent( $method, $settings ) {
$root = normalize_path( $method['dir'], true ); 
$dir_show_size = strToBool( $method['dir_show_size'] );
echo "<table class='files'>";
$is_win = isWin();
if ( 'fssource' == $method['sender'] )
if ( DIRECTORY_SEPARATOR != $root && ! $is_win || $is_win && strlen( $root ) > 3 ) {
$div_id = uniqid( "dwl_spin_", MORE_ENTROPY );
$folder_up_onclick = 'js56816a36b58dc.navFilesList("' . normalize_path( dirname( $root ) ) . '",-1,"' .
wp_create_nonce_wrapper( 'auto_save' ) . '");';
echo sprintf( 
"<tr  onclick='%s'><td><div class='folderup' style='background-image: url(img/go-back.png)' id='%s' onclick='%s'></div><a>&nbsp;..</a></td>", 
$folder_up_onclick, 
$div_id, 
$folder_up_onclick );
if ( $dir_show_size )
echo '<td align="center" class="caption">' . _esc( 'Size' ) . '</td>';
echo '</tr>';
} else
echo '<tr><td class="caption">' . _esc( 'Folder' ) . '</td><td class="caption" align="center">' .
_esc( 'Size' ) . '</td></tr>';
$chk = ( "" == $method["excludedirs"] ? "checked='checked'" : "" );
$root_id = uniqid( 'f_', MORE_ENTROPY );
echo "<tr ><td><input type='checkbox' id='root_dir' onclick='js56816a36b58dc.toggle_children(this);' name='$root_id' $chk>" .
getSpanE( $root ) . "</td>";
if ( $dir_show_size )
echo '<td>' . getHumanReadableSize( getDirSizeFromCache( $root ) ) . '</td>';
echo '</tr>';
if ( isset( $method['file_function'] ) && _is_callable( $method['file_function'] ) )
$files = _call_user_func( $method['file_function'], $root, $dir_show_size );
else
$files = array();
ksort( $files );
getFileListContent( $files, $dir_show_size, 1, $root_id, $settings );
echo "</table>";
}
function _dir_is_excluded( $dir, &$excludedirs ) {
$result = false;
foreach ( $excludedirs as $dirname ) {
$result = 0 === strpos( $dir, $dirname );
if ( $result )
break;
}
return $result;
}
function _get_excluded_dirs( $dirlist ) {
$excludedirs = empty( $dirlist ) ? array() : explode( ',', $dirlist );
array_walk( 
$excludedirs, 
function ( &$value ) {
DIRECTORY_SEPARATOR != substr( $value, - 1 ) && $value .= DIRECTORY_SEPARATOR;
} );
return $excludedirs;
}
function getFileListContent( &$dirs, $dir_show_size, $level = 1, $parent_id = null, $settings = null ) {
global $settings;
$excludedirs = _get_excluded_dirs( $settings['excludedirs'] );
$parent_id = ( empty( $parent_id ) ? uniqid( '', MORE_ENTROPY ) : $parent_id ) . ".$level";
$i = 0;
foreach ( $dirs as $fname => $dir ) {
$style['link'] = '';
if ( is_array( $dir ) ) {
$fsize = $dir['size'];
isset( $dir['desc'] ) && $fdesc = $dir['desc'];
isset( $dir['style'] ) && $style = $dir['style'];
isset( $dir['class'] ) && $class = $dir['class'];
$onclick = isset( $dir['click'] ) ? trim( $dir['click'] ) : 'js56816a36b58dc.refreshFileList(this);';
}
$chk_id = $parent_id . '.' . $i++;
$indent = 2 * $level . 'em';
$chk = '';
if ( ! _dir_is_excluded( 
$fname . ( DIRECTORY_SEPARATOR != substr( $fname, - 1 ) ? DIRECTORY_SEPARATOR : '' ), 
$excludedirs ) )
$chk = "checked='checked'";
else
$style['link'] .= ';text-decoration:line-through';
$title = ! empty( $fdesc ) ? " title='$fname' " : '';
echo "<tr" . ( ! isset( $style['row'] ) ? '' : " style='{$style['row']}'" ) .
"><td style='padding-left:$indent'><input type='checkbox' name='$chk_id' onclick='js56816a36b58dc.toggle_children(this);' $chk><a " .
( empty( $class ) ? '' : " class=\"$class\"" ) . $title .
( empty( $onclick ) ? '' : 'onclick="' . $onclick . '"' ) .
( ! empty( $style['link'] ) ? 'style="' . $style['link'] . '"' : '' ) . ">" .
( empty( $fdesc ) ? str_replace( $settings['dir'], '', $fname ) : $fdesc ) . "</a></td>";
if ( $dir_show_size )
echo '<td>' . getHumanReadableSize( $fsize ) . '</td>';
echo '</tr>';
if ( isset( $dir['child'] ) )
getFileListContent( $dir['child'], $dir_show_size, $level + 1, $chk_id, $settings );
}
}
function getFileListInfo( &$dirs, $settings = null, $level = 0 ) {
global $settings;
$excludedirs = _get_excluded_dirs( $settings['excludedirs'] );
$count = 0;
$t_size = 0;
foreach ( $dirs as $fname => $dir ) {
if ( ! _dir_is_excluded( 
$fname . ( DIRECTORY_SEPARATOR != substr( $fname, - 1 ) ? DIRECTORY_SEPARATOR : '' ), 
$excludedirs ) ) {
$count++;
if ( isset( $dir['child'] ) ) {
$r = getFileListInfo( $dir['child'], $settings, $level + 1 );
$count += $r['count'];
$t_size += $r['size']; 
} else
$t_size += $dir['size'];
}
}
return array( 'count' => $count, 'size' => $t_size );
}
?>