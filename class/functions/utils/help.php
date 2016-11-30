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
 * @version : 0.2.3-33 $
 * @commit  : 8322fc3e4ca12a069f0821feb9324ea7cfa728bd $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Nov 29 16:33:58 2016 +0100 $
 * @file    : help.php $
 * 
 * @id      : help.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\DEFAULT_JSPOPUP_WIDTH', 545 ); 
function dumpVar( $var, $pretty = false, $highlight_keys = false, $escaped = true, $output = false ) {
ob_start();
echo "<pre style='margin:0'>";
if ( $pretty ) {
$s = print_r( $var, true );
if ( $highlight_keys && is_array( $var ) ) {
$s = str_replace( 
array( 'Array', '=>' ), 
array( '<b>Array</b>', getSpan( '=>', 'gray', null, null, $escaped ) ), 
$s );
foreach ( $var as $key => $value ) {
$s = str_replace( "[$key]", '[' . getSpan( $key, 'magenta', 'bold', null, $escaped ) . ']', $s );
if ( is_array( $value ) )
$s = highlight_inner_keys( $s, $value );
}
}
echo $s;
} else
var_dump( $var );
echo "</pre>";
$buffer = ob_get_clean();
if ( ! $output )
echo $buffer;
else
return $buffer;
return true;
}
function printHelp( $html_format = false ) {
global $factory_options, $short_opts, $long_opts, $COMPRESSION_NAMES;
$table_header = function ( $id = null, $hidden = false ) {
return sprintf( 
"<table %s %s><tr style='font-weight:bold;text-align:center;background-color:#f0f0f0;'><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", 
! empty( $id ) ? "id='$id'" : '', 
$hidden ? "style='display:none'" : '', 
_esc( 'Short' ), 
_esc( 'Long' ), 
_esc( 'Argument value' ), 
_esc( 'Description' ) );
};
$arg_info = function ( $arg ) {
return false !== strpos( $arg, '::' ) ? 'optional' : ( false !== strpos( $arg, ':' ) ? 'mandatory' : '' );
};
$arg_opts = function ( $arg ) {
global $long_opts;
$result = false;
foreach ( $long_opts as $k => $o ) {
$arg == str_replace( ':', '', $o ) && $result = $k;
if ( $result )
break;
}
return $result;
};
$options = array();
if ( is_array( $factory_options ) )
foreach ( $factory_options as $group => $group_options )
foreach ( $group_options as $param_name => $param_options ) {
! empty( $param_options[1] ) && isset( $param_options[3] ) &&
$options[str_replace( ':', '', $param_options[1] )] = $param_options[3];
}
if ( ! isWin() && isset( $options['cygwin'] ) )
unset( $options['cygwin'] );
if ( ! testOSTools() ) {
if ( isset( $options['toolchain'] ) )
unset( $options['toolchain'] );
if ( isset( $options['bzipver'] ) )
unset( $options['bzipver'] );
}
$TAB = "   ";
$eol = $html_format ? '<br>' : PHP_EOL;
$out = sprintf( _esc( "<b>%s backup utility</b> (PHP CLI interface)" ), WPMYBACKUP ) . $eol;
$out .= sprintf( _esc( "Running on <red>%s + PHP %s</red>" ), PHP_OS, PHP_VERSION ) . $eol . $eol;
$out .= _esc( "Usage: php cli-backup.php [opts...]" ) . $eol;
$out .= ( ! $html_format ? $eol : '<table><tr><td colspan="3"><b>' ) . _esc( "Options:" ) .
( ! $html_format ? $eol : '</b></td></tr>' );
$header = $html_format ? $table_header() : '';
$footer = $html_format ? '</table>' : '';
$out .= $header;
if ( null !== $long_opts )
foreach ( $options as $longname => $description ) {
if ( false !== ( $key = $arg_opts( $longname ) ) && isset( $short_opts[$key] ) &&
! empty( $short_opts[$key] ) )
$short = '-' . str_replace( ':', '', $short_opts[$key] ) . ',';
else
$short = '';
$arg_nfo = $arg_info( $long_opts[$key] );
! $html_format && $out .= $TAB . sprintf( 
"%-5s --%-20s : %s %s" . $eol, 
$short, 
$longname, 
$description, 
empty( $arg_nfo ) ? '' : "($arg_nfo)" );
$html_format && $out .= sprintf( 
"<tr><td style='color:blue'>%s</td><td>--%s</td><td>%s</td><td>%s</td></tr>", 
$short, 
$longname, 
$arg_nfo, 
$description );
}
$out .= $footer;
$diff = array_flip( array_keys( $options ) );
array_walk( $diff, function ( &$item, $key ) {
$item = str_replace( ':', '', $key );
} );
$diff = array_diff_key( array_flip( $diff ), $options );
if ( count( $diff ) > 0 ) {
$tbl_id = uniqid( 'tbl_' );
$out .= $eol .
( $html_format ? "<div style='padding:3px;background-color:#f0f0f0;color:#00ADEE;cursor:pointer' onclick='var d=document.getElementById(&quot;$tbl_id&quot;);d.style.display=&quot;none&quot;==d.style.display?&quot;block&quot;:&quot;none&quot;;'>" : '' ) .
sprintf( _esc( 'Furthermore, there are (at least) %d other undocumented parameters' ), count( $diff ) ) .
( $html_format ? '</div>' : '' ) . $eol;
$out .= $table_header( $tbl_id, true );
foreach ( $diff as $longname => $key ) {
$short = str_replace( 
':', 
'', 
false !== ( $k = array_search( $key, $long_opts ) ) && isset( $short_opts[$k] ) ? $short_opts[$k] : '' );
! $html_format && $out .= $TAB . sprintf( "%-5s --%-15s" . $eol, $short, $longname );
$html_format && $out .= sprintf( 
"<tr><td style='color:blue'>%s</td><td>--%s</td><td>%s</td></tr>", 
$short, 
$longname, 
$arg_info( $key ) );
}
$out .= $footer;
}
$out .= $eol . sprintf( 
_esc( "Send bug reports at %s" ), 
( $html_format ? "<a href='mailto:" . getPluginAuthorEmail() . "'>" : '' ) . getPluginAuthorEmail() .
( $html_format ? '</a>' : '' ) ) . $eol;
echo $html_format ? $out : strip_tags( $out );
return $options;
}
function echoHelp( $msg, $quote_enclosed = true, $reuse_div = false, $title = null ) {
if ( ! preg_match( "/^\'.*\'$/", $msg ) )
$msg = "'$msg'";
echo getHelpCall( $msg, $quote_enclosed, $reuse_div, $title );
}
function getHelpCall( $msg, $quote_enclosed = true, $reuse_div = false, $title = null ) {
return ( $quote_enclosed ? '"' : '' ) . "window.jsnspace.popupWindow('" .
( empty( $title ) ? _esc( 'Help' ) : $title ) . "'," . $msg . ',' . DEFAULT_JSPOPUP_WIDTH .
( $reuse_div ? 'null,null,' . $reuse_div : '' ) . ");" . ( $quote_enclosed ? '"' : '' );
}
function getPopup( $caption, $msg, $escaped = true ) {
$escape_char = $escaped ? "\\" : '';
return "<a class=$escape_char'help$escape_char' onclick=$escape_char'jsMyBackup.popupWindow(&quot;" . _esc( 'Help' ) .
"&quot;,$msg," . DEFAULT_JSPOPUP_WIDTH . ");$escape_char'>$caption</a>";
}
function highlight_inner_keys( $s, $array, $escaped = true ) {
foreach ( $array as $key => $value ) {
$s = str_replace( "[$key]", '[' . getSpan( $key, 'blue', null, null, $escaped ) . ']', $s );
if ( is_array( $value ) )
$s = highlight_inner_keys( $s, $value, $escaped );
}
return $s;
}
function formatErrMsg( &$e, $sender = null ) {
return sprintf( 
'<red>[!] : %s</red>' . ( defined( __NAMESPACE__.'\\PHP_DEBUG_ON' && PHP_DEBUG_ON ) ? ' (%s:%d)' : '' ), 
( empty( $sender ) ? '' : $sender . '://' ) . $e->getMessage(), 
basename( $e->getFile() ), 
$e->getLine() );
}
function escape_quotes( $str, $escape = true ) {
return preg_replace( '/([\'"])/', $escape ? '\\\\' : '$1', $str );
}
if ( ! _function_exists( 'getAnchor' ) ) {
function getAnchor( $name, $anchor, $target = null, $escape = false, $popup = false ) {
$target = empty( $target ) ? '_blank' : $target;
return $popup ? getPopup( $name, '&quot;' . $anchor . '&quot;' ) : escape_quotes( 
'<a href="' . $anchor . '" target="' . $target . '">' . $name . '</a>', 
$escape );
}
}
function getAnchorE( $name, $anchor, $target = null, $popup = false ) {
return getAnchor( $name, $anchor, $target, true, $popup );
}
function readMoreHere( $url, $about_str = null, $target = '_blank', $escape = false ) {
return sprintf( 
_esc( 'Read more %s' ), 
getAnchor( empty( $about_str ) ? _esc( 'here' ) : $about_str, $url, $target, $escape ) );
}
function readMoreHereE( $url, $about_str = null, $target = '_blank' ) {
return readMoreHere( $url, $about_str, $target, true );
}
function getSpan( $str, $color = null, $font_style = null, $bg_color = null, $escape = false, $id = false ) {
$style = array();
! empty( $color ) && $style[] = "color:$color";
! empty( $bg_color ) && $style[] = "background-color:$bg_color";
! empty( $font_style ) && $style[] = sprintf( 
';font-%s:%s', 
'bold' == $font_style ? 'weight' : 'style', 
$font_style );
$style = ! empty( $style ) ? ' style="' . implode( ';', $style ) . '"' : '';
return escape_quotes( 
sprintf( "<span %s%s>$str</span>", empty( $id ) ? '' : ( 'id="' . $id . '"' ), empty( $style ) ? '' : $style ), 
$escape );
}
function getSpanE( $str, $color = null, $font_style = null, $bg_color = null ) {
return getSpan( $str, $color, $font_style, $bg_color, true );
}
function getExample( $caption, $message, $collapsed = true, $escape = true, $img_path = null, $return_array = false ) {
global $container_shape;
$id = uniqid( 'ex_' );
$result = "<div class='postbox $container_shape' id='$id' style='padding:10px'><h4 class='hintbox $container_shape' style='border-radius:5px 5px 0 0;'><div style='display:inline-block'><b>$caption</b></div></h4><div class='inside $container_shape' style='padding:10px;border:1px solid #c0c0c0;border-top:none;background-color:#f5f5f5;margin:0;border-radius:0 0 5px 5px;'>$message</div></div>";
$script = "<script>var el=document.getElementById('$id');window.jsnspace.addHeaderToggle(el.getElementsByTagName('h4'),true,'" .
( empty( $img_path ) ? plugins_url_wrapper( 'img/', IMG_PATH ) : $img_path ) . "');" .
( $collapsed ? '' : "el.getElementsByTagName('h4')[0].click();" ) . '</script>';
$x = $return_array ? '' : $script;
$result = $escape ? preg_replace( '/[\'"]/', '&quot;', $result . $x ) : $result . $x;
if ( $return_array )
return array( $result, $script );
return $result;
}
?>