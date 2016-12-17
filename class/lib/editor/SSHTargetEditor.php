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
 * @version : 1.0-2 $
 * @commit  : f8add2d67e5ecacdcf020e1de6236dda3573a7a6 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Dec 13 06:40:49 2016 +0100 $
 * @file    : SSHTargetEditor.php $
 * 
 * @id      : SSHTargetEditor.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class SSHTargetEditor extends AbstractTargetEditor {
private $_sshhost;
private $_sshuser;
private $_sshproto;
private $_ssh_publickey_file;
private $_ssh_privkey_file;
private $_dirsep;
protected function initTarget() {
parent::initTarget ();
$this->hasInfoBanner = defined ( __NAMESPACE__.'\\FILE_EXPLORER' );
$this->_sshhost = $this->settings ['sshhost'];
$this->_sshuser = $this->settings ['sshuser'];
$this->_sshproto = $this->settings ['sshproto'];
$this->_ssh_publickey_file = $this->settings ['ssh_publickey_file'];
$this->_ssh_privkey_file = $this->settings ['ssh_privkey_file'];
$this->_dirsep = '/';
$this->root = empty ( $this->root ) ? $this->_dirsep : $this->root;
$this->root = addTrailingSlash ( $this->root, $this->_dirsep );
if ('/' != $this->_dirsep)
$this->root = str_replace ( $this->_dirsep, $this->_dirsep . $this->_dirsep, $this->root );
}
protected function hideEditorContent() {
return empty ( $this->_sshhost ) || (empty ( $this->_sshuser ) && empty ( $this->_ssh_privkey_file )) || empty ( $this->root ) || ! $this->enabled || CURLPROTO_SFTP != $this->_sshproto;
}
protected function onGenerateEditorContent() {
$java_scripts = echoFolder ( $this->target_name, $this->root, $this->root, $this->ext_filter, $this->function_name, $this->_dirsep, null, $this->folder_style, false, $this->target_name, $this->settings ); 
$this->java_scripts = array_merge ( $this->java_scripts, $java_scripts );
if (! isset ( $_SESSION ['ftp_ssh_cert_info'] ))
getSSHInfo ( $this->settings );
if (isset ( $_SESSION ['ftp_ssh_cert_info'] )) {
$ssh_hint = 'This certificate guarantes that the data will be sent encrypted.';
bindSSHInfo ( 'sshhost', $_SESSION ['ftp_ssh_cert_info'], $this->java_scripts, $ssh_hint );
}
}
protected function getEditorTemplate() {
$help_1 = "'" . _esc ( 'Keep only the last n-days backups on SFTP/SSH server.<br>Leave it empty to disable this option' ) . "'";
$help_2 = "'" . _esc ( 'The SFTP/SSH ip/hostname.' ) . "'";
$selected = 'selected';
require_once $this->getTemplatePath ( 'ssh.php' );
}
protected function getExpertEditorTemplate() {
$help_1 = "'" . _esc ( 'Not implemented yet' ) . "'";
$ssh_privkey_pwd = $this->settings ['ssh_privkey_pwd'];
require_once $this->getTemplatePath ( 'ssh-expert.php' );
}
}
?>