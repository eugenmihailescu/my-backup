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
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : MailTargetEditor.php $
 * 
 * @id      : MailTargetEditor.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class MailTargetEditor extends AbstractTargetEditor {
protected function initTarget() {
parent::initTarget ();
}
protected function getEditorTemplate() {
$mail_mem_limit = getHumanReadableSize ( round ( getMemoryLimit () / 1.33, 0 ) );
$help_1 = "'" . sprintf ( _esc ( 'This allow you to send the backup attached to the e-mail.<br>Note that this may require additional settings in your php.ini file (post_max_size,upload_max_filesize,memory_limit) and/or at the e-mail server level.<br><br><b>Note</b>: use it only with small backup files as an e-mail is <a href=\\\'http://www.w3.org/Protocols/rfc1341/5_Content-Transfer-Encoding.html\\\'>base64 encoded</a> which makes your file ca. 33%% larger than it is during transfering. Your current PHP memory limit is set to %s. If you send an email larger than %s you will get a PHP memory allocation error and the process will forcibly stop.' ), ini_get ( 'memory_limit' ), $mail_mem_limit ) . "'";
$help_1 = sprintf ( $help_1, 0 !== feature_is_licensed ( 'backup2mail', $this->license [$this->license_id] ) ? '' : '<br>' . echoFeatureNotInstalled ( 'backup2mail', true ) );
$help_2 = sprintf ( _esc ( 'Specify the alternative email addresses (comma-delimited) where the backup will be sent. If not specified then the main email address %s will be used instead.' ), ! empty ( $this->settings ['email'] ) ? '(' . $this->settings ['email'] . ')' : '' );
$help_3 = sprintf ( _esc ( 'Specify the maximum size (as bytes) of the e-mail enclosed attachments. Usually this is dictated by the PHP server configuration (in your case the limit is set to MIN(post_max_size=%s, upload_max_filesize=%s)=%s and the e-mail server configuration.The backup will be equally distributed in separate messages such that the whole backup can be successfully sent without being rejected neither by the web/email server.' ), ini_get ( 'post_max_size' ), ini_get ( 'upload_max_filesize' ), getHumanReadableSize ( getUploadLimit () ) );
require_once $this->getTemplatePath ( 'backup2mail.php' );
}
protected function getExpertEditorTemplate() {
$backup2mail_smtp = strToBool ( $this->settings ['backup2mail_smtp'] );
$backup2mail_smtpauth = $backup2mail_smtp && strToBool ( $this->settings ['backup2mail_auth'] );
$backup2mail_opts_disabled = ! $backup2mail_smtp ? 'disabled' : '';
$backup2mail_auth_disabled = ! $backup2mail_smtpauth ? 'disabled' : '';
$help_4 = _esc ( 'Set this option if you want to use the PEAR::Mail library instead of the PHP default mail backend.<br>This will allow you to perform easily some benchmarks and see which backend suits you best.<br>In the future this will include also a mail queue which allows the application to enqueue the mail and send it in background.' );
$help_5 = _esc ( 'Select the mail backend application to use:<ul><li>`mail` is just the PHP built-in mail() function</li><li>`sendmail` will used the sendmail program (if available)</li><li>`smtp` will connect the SMTP server directly</li></ul>' );
$help_6 = _esc ( 'The server to connect. Default is localhost.<br>In case you want to connect via SSL then use the `ssl://` protocol in front of the server ip/address and also specify the correct port (eg. 465).' );
$help_6 .= getExample ( _esc ( 'Example' ), 'SMTP host=ssl://smtp.gmail.com<br>SMTP port=465<br>SMTP authentication=YES<br>SMTP user=johndoe@gmail.com<br>SMTP password=*******', false );
$help_7 = _esc ( 'The port to connect. Default is 25.' );
$help_8 = _esc ( 'Whether or not to use SMTP authentication. Default is NO.' );
$help_9 = _esc ( ' The username to use for SMTP authentication.' );
$help_10 = _esc ( 'The password to use for SMTP authentication.' );
$backend_options = '';
$mail_backend = array (
'mail',
'smtp',
'sendmail' 
);
foreach ( $mail_backend as $backend )
$backend_options .= sprintf ( '<option value="%s"%s>%s<option>', $backend, $backend == $this->settings ['backup2mail_backend'] ? ' selected' : '', $backend ) . '</option>';
require_once $this->getTemplatePath ( 'backup2mail-expert.php' );
}
}
?>