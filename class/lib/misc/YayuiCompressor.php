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
 * @date    : Tue Feb 16 21:41:51 2016 UTC $
 * @file    : YayuiCompressor.php $
 * 
 * @id      : YayuiCompressor.php | Tue Feb 16 21:41:51 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;

define ( __NAMESPACE__.'\\JSHINTS', 'jshints' );
define ( __NAMESPACE__.'\\SEMICOLON', 'semicolon' );
define ( __NAMESPACE__.'\\SINGLE_COMMENT', 'scomment' );
define ( __NAMESPACE__.'\\BLOCK_COMMENT', 'bcomment' );
define ( __NAMESPACE__.'\\CURLY_BRACE', 'curlybrace' );
define ( __NAMESPACE__.'\\WHITESPACE', 'whitespace' );
define ( __NAMESPACE__.'\\LINE_SEPARATOR', 'crlf' );
define ( __NAMESPACE__.'\\FUNCTION_ARGUMENT', 'fargs' );
define ( __NAMESPACE__.'\\FUNCTION_VARIABLE', 'fvars' );
define ( __NAMESPACE__.'\\MINIFY', 'minify' );
define ( __NAMESPACE__.'\\OBFUSCATE', 'obfuscate' );
define ( __NAMESPACE__.'\\PRESERVE_STRINGS', 'strings' );
function requesting_class() {
foreach ( debug_backtrace ( true ) as $stack ) {
if (isset ( $stack ['object'] )) {
return $stack ['object'];
}
}
}
class YayuiCompressor {
private $JS_HINTS = array (
'use strict' 
);
private $RESERVED = array (
'var', 
'break',
'case',
'catch',
'continue',
'default',
'delete',
'do',
'else',
'finally',
'for',
'function',
'if',
'in',
'instanceof',
'new',
'return',
'switch',
'this',
'throw',
'try',
'typeof',
'void',
'while',
'with',
'abstract',
'boolean',
'byte',
'char',
'class',
'const',
'debugger',
'double',
'enum',
'export',
'extends',
'final',
'float',
'goto',
'int',
'interface',
'implements',
'import',
'long',
'native',
'package',
'private',
'protected',
'public',
'short',
'static',
'super',
'synchronized',
'throws',
'transient',
'volatile',
'null',
'undefined',
'NaN',
'true',
'false' 
);
private $SOURCE_SEP = array (
'{',
'}' 
);
private $VALID_OPTIONS = array (
JSHINTS,
SEMICOLON,
SINGLE_COMMENT,
BLOCK_COMMENT,
CURLY_BRACE,
WHITESPACE,
LINE_SEPARATOR,
FUNCTION_ARGUMENT,
FUNCTION_VARIABLE 
);
private $OBFUSC_SEP;
private $_ratio;
private $_statistic;
function __construct() {
$this->ratio = 0;
$this->OBFUSC_SEP = array (
uniqid ( '_BEGIN_', true ),
uniqid ( '_END_', true ) 
);
}
public function getFactoryDefaults() {
return array (
PRESERVE_STRINGS => true,
JSHINTS => true,
SEMICOLON => true,
SINGLE_COMMENT => true,
BLOCK_COMMENT => true,
CURLY_BRACE => true,
WHITESPACE => true,
LINE_SEPARATOR => true,
FUNCTION_ARGUMENT => true,
FUNCTION_VARIABLE => true 
);
}
public function getOptionSet($set_id = -1) {
$obfuscate_options = array (
FUNCTION_ARGUMENT => true,
FUNCTION_VARIABLE => true 
);
switch ($set_id) {
case MINIFY :
$result = array_diff_assoc ( $this->getFactoryDefaults (), $obfuscate_options );
break;
case OBFUSCATE :
$result = $obfuscate_options;
break;
default :
$result = $this->getFactoryDefaults ();
break;
}
return $result;
}
public function getParamValue($options, $param_name) {
if (! empty ( $options ) && isset ( $options [$param_name] ) && is_bool ( $options [$param_name] ))
return $options [$param_name];
return in_array ( $param_name, $options, true );
}
private function _getVarName($nr, $base = 26) {
$digits = 1 + floor ( ($nr > 0 ? log ( $nr, $base ) : 0) );
$var_name = '';
for($i = $digits - 1; $i >= 0; $i --) {
$p = pow ( $base, $i );
$q = floor ( $nr / $p );
$var_name .= chr ( 65 + $q - ($i == 0 ? 0 : 1) );
$nr -= $q * $p;
}
if (in_array ( strtolower ( $var_name ), $this->RESERVED ))
$var_name = "_" . $var_name;
return $var_name;
}
private function _initStatistic() {
foreach ( $this->VALID_OPTIONS as $option )
$this->_statistic [$option] = array (
'size' => 0,
'time' => 0 
);
}
private function _getNextCodeBlock($data) {
$start = strpos ( $data, '{' );
$stop = strpos ( $data, '}' );
$block = substr ( $data, $start + 1, $stop - $start - 2 );
if (false === strpos ( $block, '{' ))
return substr ( $data, 0, $stop );
}
private function _getFunctionTree(&$data, $from = 0, $to = -1, $nestlevel = 0) {
$result = array ();
$in_squote = false;
$in_dquote = false;
$in_lcomment = false;
$in_bcomment = false;
if ($to < 0)
$to = strlen ( $data ) - 1;
if ($from + 8 < $to) {
if (false !== ($pos_f = $this->_stripos ( $data, 'function', $from ))) {
$pos_b = $this->_stripos ( $data, '{', $pos_f );
if (! (false === $pos_f || $pos_f > $to || false === $pos_b || $pos_b > $to)) {
$open_brace = 1;
$i = $pos_b;
while ( $i ++ < $to && $open_brace > 0 )
if ($this->_isValidChar ( $data, $i, $to, $in_squote, $in_dquote, $in_lcomment, $in_bcomment ))
$open_brace += ( int ) ($data [$i] === '{') - ( int ) ($data [$i] === '}');
$result [] = array (
$pos_f,
$i,
$pos_b + 1 < $i - 1 ? $this->_getFunctionTree ( $data, $pos_b + 1, $i - 1, $nestlevel + 1 ) : null
);
$next_result = $this->_getFunctionTree ( $data, $i + 1, $to, $nestlevel );
if (! empty ( $next_result ))
$result = array_merge ( $result, $next_result );
}
}
}
return $result;
}
private function _isValidChar(&$haystack, &$offset, &$to, &$in_squote, &$in_dquote, &$in_lcomment, &$in_bcomment) {
if (! ($in_squote || $in_dquote)) {
if ($haystack [$offset] === '/' && (($offset + 1 < $to && $haystack [$offset + 1] === '/') || ($offset > 0 && $haystack [$offset - 1] === '/'))) {
$in_lcomment = true;
} elseif ($haystack [$offset] === "\n" && $in_lcomment) {
$in_lcomment = false;
}
if ($offset > 0 && $haystack [$offset] === '*' && $haystack [$offset - 1] === '/') {
$in_bcomment = true;
} elseif ($in_bcomment && $haystack [$offset] === '/' && $haystack [$offset - 1] === '*') {
$in_bcomment = false;
}
}
if (! ($in_lcomment || $in_bcomment)) {
if ($haystack [$offset] === '"') {
$in_dquote = ! $in_dquote;
}
if ($haystack [$offset] === "'") {
$in_squote = ! $in_squote;
}
}
return ! ($in_squote || $in_dquote || $in_lcomment || $in_bcomment);
}
private function _stripos($haystack, $needle, $offset = 0) {
$in_squote = false;
$in_dquote = false;
$in_lcomment = false;
$in_bcomment = false;
$buffer = '';
$to = strlen ( $haystack );
while ( $buffer != $needle && $offset < $to ) {
if ($this->_isValidChar ( $haystack, $offset, $to, $in_squote, $in_dquote, $in_lcomment, $in_bcomment )) {
$i = strlen ( $buffer );
if (strtoupper ( $haystack [$offset] ) === strtoupper ( $needle [$i] ))
$buffer .= $haystack [$offset];
else
$buffer = '';
} else
$buffer = '';
$offset ++;
}
return $buffer == $needle ? $offset - strlen ( $buffer ) : false;
}
private function _trim($str) {
$start = microtime ();
$buffer_len = strlen ( $str );
$buffer = trim ( $str );
$this->_updateStatistic ( WHITESPACE, $buffer_len - strlen ( $buffer ), microtime () - $start );
return $buffer;
}
private function _updateStatistic($option_name = null, $count = 0, $duration = 0) {
if (! empty ( $option_name )) {
$this->_statistic [$option_name] ['size'] += $count;
$this->_statistic [$option_name] ['time'] += ($duration > 0 ? $duration : 0);
}
}
public function _preg_replace1($pattern, $replacement, $subject, $option_name = null) {
return $this->_preg_replace ( $pattern, $replacement, $subject, - 1, $rc, $option_name ); 
}
private function _preg_replace($pattern, $replacement, $subject, $limit = -1, &$count = null, $option_name = null) {
$start = microtime ();
if (null == $count)
$count = 0;
$result = call_user_func ( 'preg_replace' . (_is_callable ( $replacement ) ? '_callback' : ''), $pattern, $replacement, $subject, $limit, $count );
if (false !== $result)
$buffer = $result;
$this->_updateStatistic ( $option_name, strlen ( $subject ) - strlen ( $buffer ), microtime () - $start );
return $buffer;
}
private function _stanzaBuffer($buffer, $in_size, $out_size, $type = null, $html_separator = true) {
if ($in_size <= 0)
return $buffer;
$bs = ($html_separator ? '<!--' : '/*') . ' ';
$es = ' ' . ($html_separator ? '-->' : '*/');
return sprintf ( PHP_EOL . $bs . (empty ( $type ) ? 'Code' : $type) . ' minified by YAYUI ~%5.2f%% (%d bytes)' . $es . '%s%s', 100 * (1 - $out_size / $in_size), $in_size - $out_size, PHP_EOL, $buffer );
}
private function _sourceMinify($buffer, $options) {
if ($this->getParamValue ( $options, SEMICOLON )) {
$buffer = $this->_preg_replace1 ( '/;[\s\t|\n]*;/m', ';', $buffer, SEMICOLON );
}
if ($this->getParamValue ( $options, SINGLE_COMMENT )) {
$buffer = $this->_preg_replace1 ( '/(?<![\'":\\\\])\/\/.*/m', '', $buffer, SINGLE_COMMENT );
}
if ($this->getParamValue ( $options, BLOCK_COMMENT )) {
$buffer = $this->_preg_replace1 ( '/\/\*[\S\s]*?\*\//', '', $buffer, BLOCK_COMMENT );
}
if ($this->getParamValue ( $options, CURLY_BRACE )) {
$obj = $this;
$callback = function ($match) use(&$obj) {
extract ( array (
'this' => requesting_class () 
) );
if (preg_match_all ( '/;/', $match [0], $matches ) > 1 || preg_match_all ( '/:/', $match [0], $matches ) > 0 || preg_match_all ( '/\b(function|try|catch)\b/', $match [0], $matches ) > 0)
return $match [0];
else
return _call_user_func ( array (
$obj,
'_preg_replace1' 
), '/[\{\}]/', ' ', $match [0], CURLY_BRACE );
};
$old_buffer = null;
while ( $old_buffer != $buffer ) {
$old_buffer = $buffer;
$buffer = $obj->_preg_replace1 ( '/\b(if|else|while)\b\.*[^;\{]*(\{)([^\{\}]+)(\})/m', $callback, $buffer, CURLY_BRACE );
}
}
if ($this->getParamValue ( $options, WHITESPACE )) {
$buffer = $this->_preg_replace1 ( '/[\s\t]*([^\w\d\s\t])[\s\t]*|[ ]{2,}/', '$1', $buffer, WHITESPACE );
}
if ($this->getParamValue ( $options, LINE_SEPARATOR )) {
$buffer = $this->_preg_replace1 ( '/(?<=[\W])\n|[ ]{2,}/m', '$1', $buffer, LINE_SEPARATOR );
$buffer = $this->_preg_replace1 ( '/\n/m', ' ', $buffer, LINE_SEPARATOR );
}
return $this->_trim ( $buffer );
}
private function _obfuscateCodeBlockVars($data, $index = 0) {
if (($found = preg_match_all ( '/\{[^\{\}]*\}/m', $data, $blocks )) > 0)
foreach ( $blocks [0] as $src_block ) {
$tmp_block = str_replace ( $this->SOURCE_SEP, $this->OBFUSC_SEP, $src_block );
$obfus_block = $this->_obfuscateCodeBlockVars ( $tmp_block, $index );
$index = $obfus_block ['index'];
$block = substr ( $obfus_block ['data'], 1, strlen ( $obfus_block ['data'] ) - 2 );
if (preg_match_all ( '/\b' . $this->RESERVED [0] . '\b[^;]+/m', $block, $var_lines ) > 0) {
foreach ( $var_lines [0] as $line ) {
$short_line = substr ( $line, strpos ( $line, ' ' ) );
$short_line = $this->_preg_replace1 ( '/\/\*([^\/]|[\w\d\s\t]\/[\w\d\s\t])*\*\//m', '', $short_line, BLOCK_COMMENT );
foreach ( preg_split ( '/,/', $short_line ) as $var_def ) {
$match = preg_split ( '/=/', $var_def );
$v = trim ( $match [0] );
if (! (1 === preg_match ( '/[^\w\d]/', $v ) || in_array ( $v, $this->RESERVED ) || is_numeric ( $v ) || 0 === strlen ( $v ))) {
$obsf_name = strtolower ( $this->_getVarName ( $index ++ ) );
if ($v != $obsf_name)
do
$obfus_block ['data'] = $this->_preg_replace ( '/([\s\t\(\[\{\+\-\*\/\|<>;&%!,])(\b' . $v . '\b)([\s\t\)\]\}\+\-\*\/\|<>;&!%,.=]?)/', '$1' . $obsf_name . '$3', $obfus_block ['data'], - 1, $rc, FUNCTION_VARIABLE ); while ( $rc > 0 );
else
$index --;
}
}
}
}
$data = str_replace ( $src_block, $obfus_block ['data'], $data );
}
return array (
'found' => $found > 0,
'data' => $data,
'index' => $index 
);
}
private function _obfuscateCodeBlock($buffer, $index = 0) {
$obf_data = array (
'found' => true,
'data' => $buffer,
'index' => $index 
);
while ( $obf_data ['found'] )
$obf_data = $this->_obfuscateCodeBlockVars ( $obf_data ['data'], $obf_data ['index'] );
return str_replace ( $this->OBFUSC_SEP, $this->SOURCE_SEP, $obf_data ['data'] );
}
private function _obfuscateFunctionArgs($func_block, $options) {
$index = 0;
if ($this->getParamValue ( $options, FUNCTION_ARGUMENT ) && preg_match_all ( '/\bfunction\b[^\{]+/m', $func_block, $params ) > 0) {
foreach ( $params [0] as $vars ) {
$p1 = strpos ( $vars, '(' );
$p2 = strrpos ( $vars, ')' );
$items = substr ( $vars, $p1 + 1, $p2 - $p1 - 1 );
$items = $this->_preg_replace1 ( '/\/\*([^\/]|[\w\d\s\t]\/[\w\d\s\t])*\*\//m', '', $items, BLOCK_COMMENT );
foreach ( preg_split ( '/,/', $items ) as $param ) {
$match = preg_split ( '/[^\w\d]/', trim ( $param ) );
$v = trim ( $match [0] );
if (! (1 === preg_match ( '/[^\w\d]/', $v ) || in_array ( $v, $this->RESERVED ) || is_numeric ( $v ) || 0 === strlen ( $v ))) {
$obsf_name = strtolower ( $this->_getVarName ( $index ++ ) );
if ($v != $obsf_name)
do
$func_block = $this->_preg_replace ( '/([^\w\d\'".]*)(\b' . $v . '\b)([^\w\d\'"]*)/', '$1' . $obsf_name . '$3', $func_block, - 1, $rc, FUNCTION_ARGUMENT ); while ( $rc > 0 );
else
$index --;
}
}
}
}
if ($this->getParamValue ( $options, FUNCTION_VARIABLE ))
return $this->_obfuscateCodeBlock ( $func_block, $index );
else
return $func_block;
}
private function _obfuscateAllFunctions($data, $fct_tree, $options) {
$result = $data;
foreach ( $fct_tree as $block ) {
if (! empty ( $block [2] ))
$this->_obfuscateAllFunctions ( $data, $block [2], $options );
$src_block = substr ( $data, $block [0], $block [1] - $block [0] );
$obf_block = $this->_obfuscateFunctionArgs ( $src_block, $options );
$result = str_replace ( $src_block, $obf_block, $result );
}
return $result;
}
public function getValidOptionNames() {
return $this->VALID_OPTIONS;
}
public function getOptionDescription($name) {
switch ($name) {
case JSHINTS :
$result = 'removes the JavaScript hints (eg. "use strict" assertion)';
break;
case SEMICOLON :
$result = 'removes the unnecessary semicolons';
break;
case SINGLE_COMMENT :
$result = 'removes the single comment lines (eg. \/\/...)';
break;
case BLOCK_COMMENT :
$result = 'removes the block comment lines (eg. /*...*/)';
break;
case CURLY_BRACE :
$result = 'removes the unnecessary curly braces (if|else|while)';
break;
case WHITESPACE :
$result = 'removes the whitespaces as much as possible';
break;
case LINE_SEPARATOR :
$result = 'removes the lines separators (cr/lf)';
break;
case FUNCTION_ARGUMENT :
$result = 'obfuscates the function arguments names';
break;
case FUNCTION_VARIABLE :
$result = 'obfuscates the function code variables names';
break;
default :
$result = '';
break;
}
return $result;
}
public function getRatio() {
return $this->_ratio;
}
public function getStatistics() {
return $this->_statistic;
}
public function compress($in_file, $out_file, $options = null) {
if (! file_exists ( $in_file ))
throw new MyException ( sprintf ( _esc ( 'Input file "%s" not found.' ), $in_file ) );
$buffer = file_get_contents ( $in_file );
$in_size = filesize ( $in_file );
$buffer = $this->streamCompress ( $buffer, $options );
file_put_contents ( $out_file, $buffer );
$out_size = strlen ( $buffer );
$this->_ratio = 100 * (1 - $out_size / $in_size);
}
public function streamCompress($buffer, $options = null, $stanza = 'JavaScript', $html_stanza = true) {
if (empty ( $options ))
$options = $this->getFactoryDefaults ();
elseif (! (is_array ( $options ) )) {
throw new MyException ( "Invalid options' format." );
}
$this->_initStatistic ();
$in_size = strlen ( $buffer );
$minify = ($this->getParamValue ( $options, JSHINTS ) || $this->getParamValue ( $options, SEMICOLON ) || $this->getParamValue ( $options, SINGLE_COMMENT ) || $this->getParamValue ( $options, BLOCK_COMMENT ) || $this->getParamValue ( $options, CURLY_BRACE ) || $this->getParamValue ( $options, WHITESPACE ));
$obfuscate = ($this->getParamValue ( $options, FUNCTION_VARIABLE ) || $this->getParamValue ( $options, FUNCTION_ARGUMENT ));
if ($minify || $obfuscate) {
$removed_strings = array ();
$callback = function ($match) use(&$removed_strings) 			
{
$id = uniqid ( 'mask_', true );
$removed_strings [$id] = $match [0];
return $id;
};
if ($this->getParamValue ( $options, JSHINTS )) {
$hint_str = implode ( '|', $this->JS_HINTS );
$buffer = $this->_preg_replace1 ( '/[\'"]' . str_replace ( ' ', '[\s\t]+', $hint_str ) . '[\'"][\s\t]*;/', '', $buffer, JSHINTS );
}
if ($this->getParamValue ( $options, PRESERVE_STRINGS ))
$buffer = preg_replace_callback ( '/([\'"])(\\\\\\1|.)*?\1/m', $callback, $buffer ) . PHP_EOL;
if ($obfuscate) {
$fct_tree = $this->_getFunctionTree ( $buffer );
$buffer = $this->_obfuscateAllFunctions ( $buffer, $fct_tree, $options );
}
if ($minify)
$buffer = $this->_sourceMinify ( $buffer, $options );
if ($this->getParamValue ( $options, PRESERVE_STRINGS ))
$buffer = str_replace ( array_keys ( $removed_strings ), $removed_strings, $buffer );
}
$out_size = strlen ( $buffer );
$this->_ratio = $in_size>0?100 * (1 - $out_size / $in_size):0;
return $this->_stanzaBuffer ( $buffer, $in_size, $out_size, $stanza, $html_stanza );
}
public function htmlCompress($buffer, $options = null, $stanza = 'HTML') {
if (empty ( $options ))
$options = $this->getFactoryDefaults ();
elseif (! (is_array ( $options ) )) {
throw new MyException ( "Invalid options' format." );
}
$in_size = strlen ( $buffer );
$removed_strings = array ();
$callback = function ($matches) use(&$removed_strings) 		
{
$id = uniqid ( 'mask_', true );
$removed_strings [$id] = $matches [0];
return $id;
};
if ($this->getParamValue ( $options, PRESERVE_STRINGS ))
$buffer = preg_replace_callback ( '/(["\'])(\1|.)*?\1/m', $callback, $buffer ) . PHP_EOL; 
if ($this->getParamValue ( $options, LINE_SEPARATOR ))
$buffer = preg_replace ( '/[\r\n]+/', ' ', $buffer ); 
if ($this->getParamValue ( $options, WHITESPACE )) {
$buffer = preg_replace ( '/[\s\t]+/', ' ', $buffer ); 
$buffer = preg_replace ( '/>([\s\t\n\r]+)</', '><', $buffer ); 
}
if ($this->getParamValue ( $options, BLOCK_COMMENT ))
$buffer = preg_replace ( '/(?=<!--)([\s\S]*?)-->/', '', $buffer ); 
if ($this->getParamValue ( $options, WHITESPACE )) {
$patterns = array (
'/(<)([\s\t]+)([\w\/>]+)/m', 
'/([\w\d\/]+)(\s+)(>)/m', 
'/=([\w\d]+)([\s\t]+)([^\w\d]+)/m', 
'/([^\w\d>\'"]+)([\s\t]+)([\w\d]+)/m'  // redundant whitespaces between a non-word and a word
);
$buffer = preg_replace ( $patterns, '$1$3', $buffer );
}
if ($this->getParamValue ( $options, PRESERVE_STRINGS ))
$buffer = str_replace ( array_keys ( $removed_strings ), $removed_strings, $buffer ); 
$out_size = strlen ( $buffer );
return $this->_stanzaBuffer ( $buffer, $in_size, $out_size, $stanza );
}
}
?>