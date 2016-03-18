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
 * @file    : Array2XML.php $
 * 
 * @id      : Array2XML.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

class Array2XML {
private static $xml = null;
private static $encoding = 'UTF-8';
public static function init( $version = '1.0', $encoding = 'UTF-8', $format_output = true ) {
self::$xml = new \DomDocument( $version, $encoding );
self::$xml->formatOutput = $format_output;
self::$encoding = $encoding;
}
public static function &createXML( $node_name, $arr = array() ) {
$xml = self::getXMLRoot();
$xml->appendChild( self::convert( $node_name, $arr ) );
self::$xml = null; 
return $xml;
}
private static function &convert( $node_name, $arr = array() ) {
$xml = self::getXMLRoot();
$node = $xml->createElement( $node_name );
if ( is_array( $arr ) ) {
if ( isset( $arr['@attributes'] ) ) {
foreach ( $arr['@attributes'] as $key => $value ) {
if ( ! self::isValidTagName( $key ) ) {
throw new \Exception( 
'[Array2XML] Illegal character in attribute name. attribute: ' . $key . ' in node: ' .
$node_name );
}
$node->setAttribute( $key, self::bool2str( $value ) );
}
unset( $arr['@attributes'] ); 
}
if ( isset( $arr['@value'] ) ) {
$node->appendChild( $xml->createTextNode( self::bool2str( $arr['@value'] ) ) );
unset( $arr['@value'] ); 
return $node;
} else 
if ( isset( $arr['@cdata'] ) ) {
$node->appendChild( $xml->createCDATASection( self::bool2str( $arr['@cdata'] ) ) );
unset( $arr['@cdata'] ); 
return $node;
}
}
if ( is_array( $arr ) ) {
foreach ( $arr as $key => $value ) {
if ( ! self::isValidTagName( $key ) ) {
throw new \Exception( '[Array2XML] Illegal character in tag name. tag: ' . $key . ' in node: ' .
$node_name );
}
if ( is_array( $value ) && is_numeric( key( $value ) ) ) {
foreach ( $value as $k => $v ) {
$node->appendChild( self::convert( $key, $v ) );
}
} else {
$node->appendChild( self::convert( $key, $value ) );
}
unset( $arr[$key] ); 
}
}
if ( ! is_array( $arr ) ) {
$node->appendChild( $xml->createTextNode( self::bool2str( $arr ) ) );
}
return $node;
}
private static function getXMLRoot() {
if ( empty( self::$xml ) ) {
self::init();
}
return self::$xml;
}
private static function bool2str( $v ) {
$v = $v === true ? 'true' : $v;
$v = $v === false ? 'false' : $v;
return $v;
}
private static function isValidTagName( $tag ) {
$pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
return preg_match( $pattern, $tag, $matches ) && $matches[0] == $tag;
}
}
?>