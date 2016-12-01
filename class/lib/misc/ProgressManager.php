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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : ProgressManager.php $
 * 
 * @id      : ProgressManager.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
require_once LIB_PATH . 'MyException.php';
class ProgressManager extends GenericDataManager {
private $_lazywrite;
public function getItem( $provider, $filename ) {
$result = null;
$_data = &$this->getData();
if ( is_array( $_data ) && isset( $_data[$provider] ) ) {
$provider_item = $_data[$provider];
if ( isset( $provider_item[$filename] ) )
$result = $provider_item[$filename];
}
return $result;
}
public function addFile( $provider, $filename, $ptype = false, $running = 1 ) {
if ( null != $this->getItem( $provider, $filename ) )
throw new MyException( 
sprintf( _esc( "Key already exists: provider=%s, filename=%s" ), $provider, $filename ) );
$_data = &$this->getData();
if ( ! isset( $_data[$provider] ) )
$_data[$provider] = array();
$_data[$provider][$filename] = array( 
'bytes' => 0, 
'total_bytes' => 0, 
'ptype' => $ptype, 
'start' => time(), 
'eta' => 0, 
'speed' => 0, 
'running' => $running );
return $this->saveData();
}
public function delFile( $provider, $filename ) {
$record = $this->getItem( $provider, $filename );
if ( null == $record )
return false;
$_data = &$this->getData();
unset( $_data[$provider][$filename] );
return $this->saveData();
}
public function setProgress( 
$provider, 
$filename, 
$bytes, 
$total_bytes, 
$ptype = PT_DOWNLOAD, 
$running = 1, 
$reset_timer = false ) {
$record = $this->getItem( $provider, $filename );
if ( null == $record && ! $this->addFile( $provider, $filename, $ptype, $running ) )
return false;
$_data = &$this->getData();
if ( $this->_lazywrite ) {
$old_perc = $_data[$provider][$filename]['total_bytes'] > 0 ? round( 
100 * $_data[$provider][$filename]['bytes'] / $_data[$provider][$filename]['total_bytes'] ) : 0;
$new_perc = $total_bytes > 0 ? round( 100 * $bytes / $total_bytes ) : 0;
} else {
$old_perc = 0;
$new_perc = 0;
}
$_data[$provider][$filename]['bytes'] = $bytes;
$_data[$provider][$filename]['total_bytes'] = $total_bytes;
$reset_timer && ( $_data[$provider][$filename]['start'] = time() );
$diff = time() - $_data[$provider][$filename]['start'];
$S = $running < 0 ? $diff : ( 0 == $bytes ? 0 : ( $total_bytes * $diff / $bytes - $diff ) );
$H = floor( $S / 3600 );
$M = floor( ( $S - 3600 * $H ) / 60 );
$S = $S - 3600 * $H - 60 * $M;
$_data[$provider][$filename]['eta'] = sprintf( '%02d:%02d:%02d', $H, $M, $S );
$x = in_array( $ptype, array( PT_ADDFILE, PT_EXTRACTFILE, PT_EXECUTE, PT_ENQUEUE ) );
$v = $running < 0 ? $total_bytes : ( $diff > 0 ? $bytes / $diff : 0 );
$freq = $running < 0 ? '' : ( $x ? '/sec' : 'ps' );
$_data[$provider][$filename]['speed'] = ( $x ? ( round( $v ) . ( PT_EXECUTE == $ptype ? ' stmts' : ' files' ) ) : getHumanReadableSize( 
$v ) ) . $freq;
$_data[$provider][$filename]['running'] = $running;
return ( $running >= 0 && $this->_lazywrite && $old_perc == $new_perc ) || $this->saveData();
}
public function setLazyWrite( $value ) {
$this->_lazywrite = $value;
}
public function cleanUp() {
$result = null;
$_data = &$this->getData();
if ( is_array( $_data ) ) {
$provider = array_keys( $_data );
foreach ( $provider as $p )
foreach ( $_data[$p] as $filename => $file_info )
if ( $file_info['bytes'] == $file_info['total_bytes'] )
unset( $_data[$p][$filename] );
}
}
}
?>