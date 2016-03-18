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
 * @file    : CurlSSHWrapper.php $
 * 
 * @id      : CurlSSHWrapper.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class CurlSSHWrapper extends CurlFtpWrapper {
private $_ssh_publickey_file;
private $_ssh_privkey_file;
private $_ssh_privkey_pwd;
function __construct() {
parent::__construct ();
$codes = $this->getOKStatusCodes ();
$codes [] = 0; 
$this->setOKStatusCodes ( $codes );
}
protected function _getCurlOptions() {
$options = array (
CURLOPT_SSH_AUTH_TYPES => CURLSSH_AUTH_DEFAULT,
CURLOPT_SSL_VERIFYPEER => false 
);
$options [CURLOPT_SSH_PUBLIC_KEYFILE] = ! empty ( $this->_ssh_publickey_file ) && _file_exists ( $this->_ssh_publickey_file ) ? $this->_ssh_publickey_file : null;
if (! empty ( $this->_ssh_privkey_file ) && _file_exists ( $this->_ssh_privkey_file )) {
$options [CURLOPT_SSH_PRIVATE_KEYFILE] = $this->_ssh_privkey_file;
! empty ( $this->_ssh_privkey_pwd ) && $options [CURLOPT_KEYPASSWD] = $this->_ssh_privkey_pwd;
} else
$options [CURLOPT_SSH_PRIVATE_KEYFILE] = null;
return $options + parent::_getCurlOptions ();
}
public function setFtpParams($array_settings) {
if (! is_array ( $array_settings ))
throw new MyException ( 'Internal error. Invalid $settings param' );
$this->initFromArray ( $array_settings ); 
$ssh_options = array (
'sshhost' => '_host',
'sshport' => '_port',
'sshuser' => '_user',
'sshpwd' => '_password',
'sshproto' => '_protocol',
'ssh' => '_dir',
'ssh_throttle' => '_upl_throttle',
'ssh_publickey_file' => '_ssh_publickey_file',
'ssh_privkey_file' => '_ssh_privkey_file',
'ssh_privkey_pwd' => '_ssh_privkey_pwd' 
);
foreach ( $ssh_options as $key => $value )
isset ( $array_settings [$key] ) && $this->$value = $array_settings [$key];
}
public function getSSHInfo() {
$ok_status_codes = array (
0 
);
$this->setExcludeBody ();
$this->setOKStatusCodes ( $ok_status_codes );
try {
$this->curlPOST ( $this->_getUrl ( null, true ) );
} catch ( MyException $e ) {
if (CURLE_SSH != $e->getCode ())
throw new MyException ( $e->getMessage (), $e->getCode (), $e->getPrevious () );
}
return parent::getSSHInfo ();
}
public function deleteFile($filename, $_is_dir = false, $bool_output = true) {
if (CURLPROTO_SCP == $this->_protocol) {
throw new MyException ( 'Delete not available for SCP protocol' );
} else
return parent::deleteFile ( $filename, $_is_dir, $bool_output );
}
public function getFtpFiles($path = '') {
if (CURLPROTO_SCP == $this->_protocol) {
throw new MyException ( 'File list not available for SCP protocol' );
} else
return parent::getFtpFiles ( $path );
}
public function ftpExecRawCmds($raw_cmd, $cmd_params = null) {
if (CURLPROTO_SCP == $this->_protocol) {
throw new MyException ( 'Execute Raw Command not available for SCP protocol' );
} else
return parent::ftpExecRawCmds ( $raw_cmd, $cmd_params );
}
}
?>