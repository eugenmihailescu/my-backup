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
 * @file    : NonceLib.php $
 * 
 * @id      : NonceLib.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define( __NAMESPACE__.'\\WPMYBAK_NONCE_UNIQUE_KEY', 'isGzXX8m9u0OtrwxL+meLep9dTHAfgbMelm1DXvPXu4=' ); 
define( __NAMESPACE__.'\\WPMYBAK_NONCE_LIFESPAN', 3600 ); 
class NonceLib {
private $_user_id;
private $_nonce_file;
private $_nonce_lifespan;
private function _calcNonce( $str ) {
return crc32( $str );
}
private function load_nonces() {
if ( _file_exists( $this->_nonce_file ) )
$nonces = json_decode( file_get_contents( $this->_nonce_file ), true );
else
return array();
$valid_nonces = array();
if ( null != $nonces )
foreach ( $nonces as $user_id => $user_nonces ) {
if ( ! isset( $valid_nonces[$user_id] ) )
$valid_nonces[$user_id] = array();
foreach ( $user_nonces as $nonce ) {
$time = substr( $nonce, - 10 );
if ( time() - intval( $time ) <= $this->_nonce_lifespan )
$valid_nonces[$user_id][] = $nonce;
}
}
return $valid_nonces;
}
private function store_nonce( $nonce ) {
$nonces = $this->load_nonces();
if ( ! key_exists( $this->_user_id, $nonces ) )
$nonces[$this->_user_id] = array();
if ( in_array( $nonce, $nonces[$this->_user_id] ) )
return; 
$nonces[$this->_user_id][] = $nonce;
file_put_contents( $this->_nonce_file, json_encode( $nonces ) );
}
function __construct( $user_id, $nonce_file = null, $lifespan = WPMYBAK_NONCE_LIFESPAN ) {
$this->_user_id = $user_id;
$this->_nonce_file = empty( $nonce_file ) ? ( defined( __NAMESPACE__.'\\LOG_DIR' ) ? LOG_DIR : _sys_get_temp_dir() ) .
DIRECTORY_SEPARATOR . get_class( $this ) . '-nonces.log' : $nonce_file;
$this->_nonce_lifespan = $lifespan;
}
public function create_nonce( $action ) {
$nonce = $this->_calcNonce( $action . $this->_user_id ) . time();
$this->store_nonce( $nonce );
return $nonce;
}
public function verify_nonce( $nonce, $action ) {
$time = substr( $nonce, - 10 );
if (time() - intval( $time ) > $this->_nonce_lifespan ) {
return false;
}
return $this->_calcNonce( $action . $this->_user_id ) == substr( $nonce, 0, strlen( $nonce ) - 10 );
}
}
?>