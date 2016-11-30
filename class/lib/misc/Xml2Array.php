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
 * @file    : Xml2Array.php $
 * 
 * @id      : Xml2Array.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\WEBDAV_TEXT_KEY', '#text' );
class Xml2Array {
private $_dom;
private $_cache;
private $_namespace;
private function _getNamespace() {
$dom_array = $this->toArray();
if ( count( $dom_array ) > 0 && false !== reset( $dom_array ) &&
preg_match( '/([^:]+):/', key( $dom_array ), $matches ) )
return $matches[1];
return false;
}
function __construct( $xml_string = null, $format_output = false, $version = '1.0', $encoding = 'UTF-8' ) {
$this->_dom = null;
$this->_cache = null;
if ( is_string( $xml_string ) ) {
$this->dom = new \DOMDocument( $version, $encoding );
$this->dom->formatOutput = $format_output;
$this->dom->loadXml( $xml_string );
$this->_cache = ( null == $this->dom ? false : $this->_xmlNode2Array( $this->dom ) ); 
$this->_namespace = $this->_getNamespace();
}
}
function _xmlNode2Array( $node ) {
$occurance = array();
$result = '';
if ( isset( $node->childNodes ) )
foreach ( $node->childNodes as $child )
$occurance[$child->nodeName] = isset( $occurance[$child->nodeName] ) ? $occurance[$child->nodeName] + 1 : 1;
if ( $node->nodeType == XML_TEXT_NODE )
$result = html_entity_decode( 
htmlentities( $node->nodeValue ));
else {
if ( $node->hasChildNodes() ) {
$children = $node->childNodes;
for ( $i = 0; $i < $children->length; $i++ ) {
$child = $children->item( $i );
if ( WEBDAV_TEXT_KEY != $child->nodeName ) {
if ( $occurance[$child->nodeName] > 1 )
$result[$child->nodeName][] = $this->_xmlNode2Array( $child );
else
$result[$child->nodeName] = $this->_xmlNode2Array( $child );
} else 
{
$text = trim( $this->_xmlNode2Array( $child ) );
if ( ! empty( $text ) )
$result[$child->nodeName] = $this->_xmlNode2Array( $child );
}
}
}
if ( $node->hasAttributes() ) {
$attributes = $node->attributes;
if ( ! is_null( $attributes ) )
foreach ( $attributes as $attr )
$result["@" . $attr->name] = $attr->value;
}
}
return $result;
}
function toArray() {
if ( null == $this->_cache )
$this->_cache = ( null == $this->dom ? false : $this->_xmlNode2Array( $this->dom ) );
return $this->_cache;
}
function getValueByPath( $path, $root = null ) {
$found = true;
$path = explode( ' ', $path ); 
$dom_array = null == $root ? $this->toArray() : $root;
foreach ( $path as $path_key ) {
$key = $this->_namespace . ':' . $path_key;
if ( isset( $dom_array[$key] ) )
$dom_array = $dom_array[$key];
else {
$found = false;
break;
}
}
return ! $found ? false : $dom_array;
}
function getNamespace() {
return $this->_namespace;
}
}
?>