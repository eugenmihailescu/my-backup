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
 * @version : 0.2.0-10 $
 * @commit  : bc79573e2975a220cb1cfbb08b16615f721a68c5 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Sep 14 21:14:57 2015 +0200 $
 * @file    : SimpleLogin.php $
 * 
 * @id      : SimpleLogin.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

define ( "SIMPLELOGIN_ALLOWED_USERS", "demo,sandbox" ); 
define ( "SIMPLELOGIN_ALLOWED_USERS_PWD", "sr2jfh9e!4^O@S,ji!z4ExBkF!C*s" ); 
define ( "SIMPLELOGIN_ALLOWED_USERS_EMAIL", "eugenmihailescux@gmail.com," ); 
define ( "SIMPLELOGIN_ALLOWED_USERS_SECRETS", "1234567890," ); 
define ( "SIMPLELOGIN_DEFAULT_PASSWORD_ENTROPY", 80 ); 
define ( "SIMPLELOGIN_LOGIN_BOX_BORDER", "border: solid 1px #00adee; border-radius: 10px; padding: 10px" );
define ( "SIMPLELOGIN_LOG_FILENAME", md5 ( "SimpleLogin" ) );
define ( "SIMPLELOGIN_LOGIN_RETRY_LIMIT", 1 ); 
define ( "SIMPLELOGIN_SESSION_USERNAME", 'simple_login_username' ); 
if (! defined ( "SIMPLELOGIN_SESSION_LOGGED" ))
define ( "SIMPLELOGIN_SESSION_LOGGED", 'simple_login_is_logged' ); 
if (! defined ( 'LOCALE_DEFAULT_CHARSET' ))
define ( 'LOCALE_DEFAULT_CHARSET', 'utf8' );
class SimpleLogin {
private $_is_logged;
private $_force_ssl;
private $_password_entropy;
private $_password_strength_callback;
private $_login_title;
private $_allow_password_recovery;
private $_password_recovery_url;
private $_log_file;
public $java_scripts;
public $onCheckJavaScript;
private function _insertHTMLSection($section_name, $ending = false) {
$separator = PHP_EOL . '<!-- ' . ($ending ? '' : ':-) ') . str_repeat ( '/', 40 ) . '  %s %s here ' . str_repeat ( '\\', 40 ) . ($ending ? ' :-(' : '') . ' -->' . PHP_EOL;
echo sprintf ( $separator, $section_name, $ending ? _ ( 'ends' ) : _ ( 'starts' ) );
}
private function isSSL() {
$result = false;
if (! empty ( $_SERVER ['HTTPS'] ) && true == strToBool ( $_SERVER ['HTTPS'] ) || ! empty ( $_SERVER ['SERVER_PORT_SECURE'] ) || 443 == $_SERVER ['SERVER_PORT'])
$result = true;
return $result;
}
private function getSSLIcon() {
if (! $this->isSSL ()) {
$icon = 'security-high.png';
$title = _ ( 'Warning' );
$function = "js55f82caaae905.popupError";
$msg = _ ( "Password fields present on an insecure (http://) page.<br>This is a security risk that allows user login credentials<br>to be stolen.<br><b>Solution</b> : connect the server by using its SSL certificate,<br>ie. replace <i>http</i> with http<span style=\\\"color:red;font-weight:bold\\\">s</span> in the browser address bar.<br>You will want to install one (if it does not already exist)<br>in order to:<ol type=\\\"i\\\"><li>hide the information that you send/receive from server</li><li>make sure the computer you are talking to is the one<br>you trust</li></ol>" );
$msg .= "<a href=\\\"https://www.youtube.com/watch?v=SJJmoDZ3il8\\\" target=\\\"_blank\\\">" . _ ( 'The short story' ) . "</a>. ";
$msg .= "<a href=\\\"https://www.youtube.com/watch?v=iQsKdtjwtYI\\\" target=\\\"_blank\\\">" . _ ( 'The long story' ) . "</a>. ";
$msg .= "<a href=\\\"https://developer.mozilla.org/docs/Security/InsecurePasswords\\\" target=\\\"_blank\\\">" . _ ( 'Just read this' ) . "</a>.";
} else {
$icon = 'security-low.png';
$title = _ ( 'Notice' );
$function = "js55f82caaae905.popupWindow";
$msg = _ ( "Password fields present on an secure (https://) page.<br>Thanks to the SSL your password is safe, nobody between this<br>PC and the web server can read/stole your password likewise<br>no other data send/received between these two machines.<br>Although <a href=\\\"http://lmgtfy.com/?q=SSL\\\" target=\\\"_blank\\\">Google Is Your Friend</a> (GIYF) in this case I warmly<br>recommend searching the Wikipedia about <a href=\\\"http://en.wikipedia.org/wiki/Transport_Layer_Security\\\" target=\\\"_blank\\\">SSL</a>." );
}
return "<img id='ssl_alert' style='vertical-align:middle;cursor:help' src='img/$icon' onclick='$function(\"$title\",\"$msg\");'/>";
}
public function checkIfBruteForce($client_ip, $proxy_ip) {
if (file_exists ( $this->_log_file ))
$log_data = json_decode ( file_get_contents ( $this->_log_file ), true );
else
$log_data = array ();
$key = array ();
if (! empty ( $client_ip ))
$key [] = $client_ip;
if (! empty ( $proxy_ip ))
$key [] = $proxy_ip;
$key = implode ( ';', $key );
$now = time ();
$brute_force = false;
if (! empty ( $key )) {
if (isset ( $log_data [$key] )) {
$brute_force = ($now - $log_data [$key] ['timestamp'] < SIMPLELOGIN_LOGIN_RETRY_LIMIT || ($now - $log_data [$key] ['timestamp'] < 3600 && $log_data [$key] ['count'] > 3600 / SIMPLELOGIN_LOGIN_RETRY_LIMIT));
$log_data [$key] ['timestamp'] = $now;
$log_data [$key] ['count'] ++;
} else
$log_data [$key] = array (
'timestamp' => $now,
'count' => 1 
);
mkdir ( _dirname ( $this->_log_file ) );
file_put_contents ( $this->_log_file, json_encode ( $log_data ) );
}
return $brute_force;
}
public function isLogged() {
if ($this->_force_ssl && ! $this->isSSL ())
return false;
return (isset ( $_SESSION [SIMPLELOGIN_SESSION_LOGGED] ) && true == $_SESSION [SIMPLELOGIN_SESSION_LOGGED]);
}
function __construct($log_path = null, $force_ssl = false) {
$this->onCheckJavaScript = null;
$this->java_scripts = array ();
if (empty ( $log_path ))
$log_path = sys_get_temp_dir ();
$log_path .= substr ( $log_path, - 1 ) != DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '';
$this->_log_file = $log_path . SIMPLELOGIN_LOG_FILENAME . '.log';
$this->_allow_password_recovery = false;
$this->_password_recovery_url = null;
$this->_password_entropy = 0;
$this->_password_strength_callback = null;
$this->_force_ssl = $force_ssl;
is_session_started ();
$this->_is_logged = $this->isLogged ();
$_SESSION [SIMPLELOGIN_SESSION_LOGGED] = $this->_is_logged;
$this->_login_title = _ ( 'Login into system' );
}
public function loginUser($username, $password) {
$allowed_users = explode ( ',', SIMPLELOGIN_ALLOWED_USERS );
$allowed_users_pwd = explode ( ',', SIMPLELOGIN_ALLOWED_USERS_PWD );
$user_key = array_search ( $username, $allowed_users );
if (false !== $user_key && $allowed_users_pwd [$user_key] == $password) {
$is_logged = true;
$_SESSION [SIMPLELOGIN_SESSION_USERNAME] = $username;
} else {
unset ( $_SESSION [SIMPLELOGIN_SESSION_USERNAME] );
$is_logged = false;
}
$_SESSION [SIMPLELOGIN_SESSION_LOGGED] = $is_logged;
return $is_logged;
}
public function logout() {
$_SESSION = array ();
if (ini_get ( "session.use_cookies" )) {
$params = session_get_cookie_params ();
setcookie ( session_name (), '', time () - 86400, $params ["path"], $params ["domain"], $params ["secure"], $params ["httponly"] );
}
}
public function setOnPasswordStrength($callback) {
$this->_password_strength_callback = $callback;
}
public function setEnforceStrongPassword($entropy = SIMPLELOGIN_DEFAULT_PASSWORD_ENTROPY) {
$this->_password_entropy = $entropy;
}
public function setEnforceSSL($force_ssl = true) {
$this->_force_ssl = $force_ssl;
}
public function setLoginTitle($title) {
$this->_login_title = $title;
}
public function allowPasswordRecovery($allow = true, $recovery_url = null) {
$this->_allow_password_recovery = $allow;
$this->_password_recovery_url = $recovery_url;
}
public function loginForm() {
$help = sprintf ( "'%s:<br><ul>", _ ( 'The login button is disabled because' ) );
if ($this->_password_entropy > 0)
$help .= "<li>" . _ ( 'the login policy enforces the use of strong password<br>and yours is not yet that strong' ) . '<br>' . _ ( "Just replace the <i>http</i>:// protocol with the http<span style=color:red;font-weight:bold>s</span>:// in the browser address bar" ) . "</li></ul>or<ul>";
if ($this->_force_ssl)
$help .= "<li>" . _ ( 'the login policy enforces the use SSL protocol and<br>your web address does not indicate the use of SSL' ) . "</li>";
$help .= "</ul>'";
if (_is_callable ( $this->onCheckJavaScript ))
_call_user_func ( $this->onCheckJavaScript );
$section_name1 = _ ( 'Simple login form' );
$this->_insertHTMLSection ( $section_name1 );
?>
<form id="login_form" action=<?php echo "'".selfURL()."' ";?> method="post"
style="position: relative">
<table
style="min-width:400px;margin-left: auto; margin-right: auto; <?php echo SIMPLELOGIN_LOGIN_BOX_BORDER;?>
">
<tr>
<td colspan="3"
style='text-align: center; font-weight: bold; font-size: 1.25em;'><?php echo $this->_login_title;?></td>
</tr>
<tr>
<td><label for="username"><?php _pesc('User name:');?></label></td>
<td><input type="text" name="username" id="username" autocomplete="off"
onchange="pwd_change(document.getElementById('password').value);"
onkeydown="this.onchange()" onkeyup="this.onchange()"></td>
</tr>
<tr>
<td style="min-width: 93px"><label for="password"><?php _pesc('Password:');?></label></td>
<td style="min-width: 182px;"><input type="password" name="password"
id="password" onchange="pwd_change(this.value);"
onkeydown="this.onchange()" onkeyup="this.onchange()" style="<?php echo !$this->isSSL()?"background-color:red;":"";?>"><?php echo $this->getSSLIcon();?></td>
<td style="min-width: 91px;" id="td_pwd_strength"><span
style="padding-bottom: 1px; border-style: none none solid; border-width: medium medium thick; border-color: #c0c0c0"
id="pwd_strength"></span></td>
</tr>
<tr>
<td colspan="3" align="center"><input type="button" class="button"
id="btn_login" onclick="document.getElementById('login_form').submit();"
value="<?php _pesc('Log in');?>"><a class='help' id='btn_login_hint'
style="display: none" onclick=<?php echo echoHelp($help);?>> [?]</a>
</tr>
<?php
if ($this->_allow_password_recovery && ! empty ( $this->_password_recovery_url )) {
?>
<tr>
<td colspan="3" style='text-align: center'><a href='#'
onclick="document.getElementById('pwd_recovery_row').setAttribute('style','display:table-row');<?php echo echoHelp("'"._('You may recover your username`s password by providing<br>the secret that you have set for your password.<br>The password will be sent at the username`s email<br>address.')."'",false);?>"><?php _pesc('Forgot your password?'); ?></a></td>
</tr>
<tr id="pwd_recovery_row" style="display: none">
<td><label for="pwd_recovery"><?php _pesc('Secret');?></label></td>
<td><input type="text" id="pwd_recovery"></td>
<td><input type="button" class="button-primary" value="Recovery"
onclick="recovery_pwd('<?php echo $this->_password_recovery_url;?>');"></td>
</tr>
<?php
}
?>
</table>
</form>
<?php
$section_name = $section_name1 . ' javascript';
$this->_insertHTMLSection ( $section_name );
?>
<script type="text/javascript">	
function recovery_pwd(url)
{
var username=document.getElementById('username').value,secret=document.getElementById('pwd_recovery').value;
if(username.length==0 || secret.length==0)
<?php printf("return js55f82caaae905.popupError('%s','<b>%s</b> and <b>%s</b> %s');",_('Error'),_('Username'),_('Secret'),_('must not be empty'));?>
js55f82caaae905.asyncGetContent(url,"action=login_recovery&username="+username+"&secret="+secret);
}
function set_login_state(entropy)
{
document.getElementById('btn_login').disabled=entropy< <?php echo $this->_password_entropy.($this->_force_ssl?' || location.protocol!="https:"':'');?>;
document.getElementById('btn_login_hint').setAttribute('style','display:'+(true==document.getElementById('btn_login').disabled?'inline':'none'));
}
function pwd_change(password)
{
var okd,a,el,obj=<?php if(!empty($this->_password_strength_callback))echo $this->_password_strength_callback.'(password)';else '""';?>;
el=document.getElementById('pwd_strength');
el.innerHTML=obj.strength;
el.style.color=obj.color;
el.style.borderColor=obj.color;
set_login_state(obj.entropy);
}
set_login_state(0);
var okd = document.onkeydown;
document.onkeydown = function(a) {
var a = a || window.event;
if (a.keyCode == 13) document.getElementById('btn_login').onclick();
if (okd)okd(a);
};
<?php
! $this->isSSL () && print "setInterval(function(){if(typeof fadeIn == 'undefined'||typeof fadeOut == 'undefined')return;var el=document.getElementById('ssl_alert');fadeOut(el);fadeIn(el);fadeIn(el,'inline-block');},5000);";
! empty ( $this->java_scripts ) && print (PHP_EOL . '// Custom login initialization script' . PHP_EOL) ;
echo implode ( PHP_EOL, $this->java_scripts );
?>
</script>
<?php
$this->_insertHTMLSection ( $section_name, true );
$this->_insertHTMLSection ( $section_name1, true );
}
public function recoverUserPassword($username, $secret) {
$allowed_users = explode ( ',', SIMPLELOGIN_ALLOWED_USERS );
$allowed_users_pwd = explode ( ',', SIMPLELOGIN_ALLOWED_USERS_PWD );
$allowed_users_email = explode ( ',', SIMPLELOGIN_ALLOWED_USERS_EMAIL );
$allowed_users_secret = explode ( ',', SIMPLELOGIN_ALLOWED_USERS_SECRETS );
$user_key = array_search ( $username, $allowed_users );
$usersecret_key = array_search ( $secret, $allowed_users_secret );
if (! (false === $user_key || false === $usersecret_key)) {
$email = $allowed_users_email [$user_key];
$password = $allowed_users_pwd [$user_key];
} else {
throw new MyException ( _ ( 'Bad username or secret' ) );
}
return @mail ( $email, _ ( 'Password recovery' ), sprintf ( _ ( "Your password is: %s" ), $password ) );
}
public function recoverPassword() {
$empty_fld = null;
if (empty ( $_POST ['secret'] ))
$empty_fld = 'secret';
elseif (empty ( $_POST ['username'] ))
$empty_fld = 'username';
if (! empty ( $empty_fld ))
throw new MyException ( sprintf ( _ ( "Cannot recovery password. The provided %s is empty." ), $empty_fld ) );
return $this->recoverUserPassword ( $_POST ['username'], $_POST ['secret'] );
}
}
?>