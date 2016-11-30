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
 * @file    : SimpleLogin.php $
 * 
 * @id      : SimpleLogin.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

defined ( __NAMESPACE__.'\\SIMPLELOGIN_PWD_FILE' ) || define ( __NAMESPACE__.'\\SIMPLELOGIN_PWD_FILE', $_SERVER ['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'config/.simplepwd' );
define ( __NAMESPACE__."\\SIMPLELOGIN_PASSWORD_ALGORITHM", PASSWORD_DEFAULT ); 
define ( __NAMESPACE__."\\SIMPLELOGIN_DEFAULT_PASSWORD_ENTROPY", 80 ); 
define ( __NAMESPACE__."\\SIMPLELOGIN_LOGIN_BOX_BORDER", "border: solid 1px #00adee; border-radius: 10px; padding: 10px" );
define ( __NAMESPACE__."\\SIMPLELOGIN_LOG_FILENAME", md5 ( "SimpleLogin" ) );
define ( __NAMESPACE__."\\SIMPLELOGIN_LOGIN_RETRY_LIMIT", 1 ); 
define ( __NAMESPACE__."\\SIMPLELOGIN_SESSION_USERNAME", 'simple_login_username' ); 
define ( __NAMESPACE__."\\SIMPLELOGIN_DEMO_USERNAME", 'demo' ); 
define ( __NAMESPACE__."\\SIMPLELOGIN_DEMO_PASSWORD", 'demo@mybackup' ); 
if (! defined ( __NAMESPACE__."\\SIMPLELOGIN_SESSION_LOGGED" ))
define ( __NAMESPACE__."\\SIMPLELOGIN_SESSION_LOGGED", 'simple_login_is_logged' ); 
if (! defined ( __NAMESPACE__.'\\LOCALE_DEFAULT_CHARSET' ))
define ( __NAMESPACE__.'\\LOCALE_DEFAULT_CHARSET', 'utf8' );
class PasswordManager {
private $_users;
private $_filename;
private $_algorithm;
private function loadFromFile($filename) {
$result = false;
if ($fr = fopen ( $filename, 'rb' )) {
$result = array ();
while ( $line = fgets ( $fr ) )
if (preg_match ( '/^[^\s\#].*/m', $line )) {
$info = explode ( ':', $line );
$user = $info [0];
$hash = isset ( $info [1] ) ? $info [1] : false;
$email = isset ( $info [2] ) ? $info [2] : false;
$secret = isset ( $info [3] ) ? $info [3] : false;
$result [$user] = array ('hash' => $hash,'email' => $email,'secret' => $secret );
}
fclose ( $fr );
}
return $result;
}
function __construct($filename = null, $algorithm = SIMPLELOGIN_PASSWORD_ALGORITHM) {
if (version_compare ( PHP_VERSION, '5.5.0', '<' ))
throw new \Exception ( sprintf ( _ ( 'Class %s requires PHP v5.5.0+' ), __CLASS__ ) );
$this->_algorithm = $algorithm;
is_file ( $filename ) && $this->_users = $this->loadFromFile ( $filename );
$this->_filename = $filename;
}
public function userExists($username) {
return isset ( $this->_users [$username] );
}
public function getUserCount() {
return count ( $this->_users );
}
public function addUser($username, $password, $email = false, $secret = false) {
if (isset ( $this->_users [$username] ))
throw new \Exception ( sprintf ( _ ( 'User %s already exists' ), $username ) );
if (empty ( $password ))
throw new \Exception ( _ ( 'Empty password not allowed' ) );
$pwd_hash = password_hash ( $password, $this->_algorithm );
$secret_hash = password_hash ( $secret, $this->_algorithm );
$this->_users [$username] = array ('hash' => $pwd_hash,'secret' => $secret_hash );
$this->setUserEmail ( $username, $email );
}
public function delUser($username) {
if (! isset ( $this->_users [$username] ))
throw new \Exception ( sprintf ( _esc ( 'User %s does not exists on %s' ), $username, $this->_filename ) );
unset ( $this->_users [$username] );
$this->saveToFile ();
}
public function verifyPassword($username, $password) {
if (! isset ( $this->_users [$username] ))
throw new \Exception ( sprintf ( _ ( 'User %s does not exist' ), $username ) );
$hash = $this->_users [$username] ['hash'];
return password_verify ( $password, $hash );
}
public function verifySecret($username, $secret) {
if (! isset ( $this->_users [$username] ))
throw new \Exception ( sprintf ( _ ( 'User %s does not exist' ), $username ) );
$hash = $this->_users [$username] ['secret'];
if (password_verify ( $secret, $hash ))
return $this->_users [$username] ['email'];
return false;
}
public function setUserPassword($username, $password, $secret = false) {
if (! isset ( $this->_users [$username] ))
throw new \Exception ( sprintf ( _ ( 'User %s does not exist' ), $username ) );
if (empty ( $password ))
throw new \Exception ( _ ( 'Empty password not allowed' ) );
$pwd_hash = password_hash ( $password, $this->_algorithm );
$secret_hash = password_hash ( $secret, $this->_algorithm );
$this->_users [$username] ['hash'] = $pwd_hash;
$secret && $this->_users [$username] ['secret'] = $secret_hash;
$this->saveToFile ();
}
public function setUserEmail($username, $email) {
if (! isset ( $this->_users [$username] ))
throw new \Exception ( sprintf ( _ ( 'User %s not found' ), $username ) );
$pattern = "\A[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\z";
if (! preg_match ( '/' . $pattern . '/', $email ))
throw new \Exception ( sprintf ( _ ( 'The email address %s is not valid' ), $email ) );
$this->_users [$username] ['email'] = $email;
$this->saveToFile ();
}
public function saveToFile($filename = null) {
empty ( $filename ) && $filename = $this->_filename;
if (empty ( $filename ))
throw new \Exception ( _esc ( 'The filename is invalid' ) );
array_walk ( $this->_users, function (&$item, $key) {
$item = sprintf ( '%s:%s:%s:%s', $key, $item ['hash'], $item ['email'], $item ['secret'] );
} );
file_put_contents ( $filename, implode ( PHP_EOL, $this->_users ) );
}
}
class SimpleLogin {
private $_pwd_manager;
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
$function = "jsMyBackup.popupError";
$msg = _ ( "Password fields present on an insecure (http://) page.<br>This is a security risk that allows user login credentials<br>to be stolen.<br><b>Solution</b> : connect the server by using its SSL certificate,<br>ie. replace <i>http</i> with http<span style=\\\"color:red;font-weight:bold\\\">s</span> in the browser address bar.<br>You will want to install one (if it does not already exist)<br>in order to:<ol type=\\\"i\\\"><li>hide the information that you send/receive from server</li><li>make sure the computer you are talking to is the one<br>you trust</li></ol>" );
$msg .= "<a href=\\\"https://www.youtube.com/watch?v=SJJmoDZ3il8\\\" target=\\\"_blank\\\">" . _ ( 'The short story' ) . "</a>. ";
$msg .= "<a href=\\\"https://www.youtube.com/watch?v=iQsKdtjwtYI\\\" target=\\\"_blank\\\">" . _ ( 'The long story' ) . "</a>. ";
$msg .= "<a href=\\\"https://developer.mozilla.org/docs/Security/InsecurePasswords\\\" target=\\\"_blank\\\">" . _ ( 'Just read this' ) . "</a>.";
} else {
$icon = 'security-low.png';
$title = _ ( 'Notice' );
$function = "jsMyBackup.popupWindow";
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
$log_data [$key] = array ('timestamp' => $now,'count' => 1 );
$dir = _dirname ( $this->_log_file );
is_dir ( $dir ) || mkdir ( $dir );
file_put_contents ( $this->_log_file, json_encode ( $log_data ) );
}
return $brute_force;
}
private function _passwordEntropy($password) {
$l = strlen ( $password );
$n = 1 + ord ( '~' ) - ord ( '!' );
$total = pow ( $n, $l );
$h = $l * log ( $n, 2 );
return $h;
}
public function isLogged() {
if ($this->_force_ssl && ! $this->isSSL ())
return false;
return (isset ( $_SESSION [SIMPLELOGIN_SESSION_LOGGED] ) && true == $_SESSION [SIMPLELOGIN_SESSION_LOGGED]);
}
function __construct($log_path = null, $force_ssl = false) {
$this->_pwd_manager = new PasswordManager ( SIMPLELOGIN_PWD_FILE );
$this->onCheckJavaScript = null;
$this->java_scripts = array ();
if (empty ( $log_path ))
$log_path = defined ( __NAMESPACE__.'\\LOG_DIR' ) ? LOG_DIR : sys_get_temp_dir ();
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
$is_logged = false;
try {
if ($this->_pwd_manager->verifyPassword ( $username, $password )) {
$is_logged = true;
$_SESSION [SIMPLELOGIN_SESSION_USERNAME] = $username;
}
} catch ( \Exception $e ) {
}
if (! $is_logged) {
unset ( $_SESSION [SIMPLELOGIN_SESSION_USERNAME] );
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
$users_not_defined = $this->_pwd_manager->userExists ( SIMPLELOGIN_DEMO_USERNAME ) && ($this->_pwd_manager->getUserCount () == 1) || (! $this->_pwd_manager->getUserCount ());
$help = sprintf ( "'%s:<br><ul>", _ ( 'The login button is disabled because' ) );
if ($this->_password_entropy > 0)
$help .= "<li>" . _ ( 'the login policy enforces the use of strong password<br>and yours is not yet that strong' ) . '<br>' . _ ( "Just replace the <i>http</i>:// protocol with the http<span style=color:red;font-weight:bold>s</span>:// in the browser address bar" ) . "</li></ul>or<ul>";
if ($this->_force_ssl)
$help .= "<li>" . _ ( 'the login policy enforces the use SSL protocol and<br>your web address does not indicate the use of SSL' ) . "</li>";
$help .= "</ul>'";
$help_simplepwd = sprintf ( '%s<br>%s%s<pre>%s</pre>', _esc ( 'It seems that you have not defined any user:password for this application.' ), _esc ( 'See the default password file below' ), ' (ROOT/'. str_replace ( ROOT_PATH, '', SIMPLELOGIN_PWD_FILE ).') :', str_replace ( PHP_EOL, '<br>', htmlspecialchars ( str_replace ( array (
"'",'"' ), array ("\\'",'\\"' ), file_get_contents ( SIMPLELOGIN_PWD_FILE ) ), ENT_QUOTES ) ) );
if (_is_callable ( $this->onCheckJavaScript ))
_call_user_func ( $this->onCheckJavaScript );
$section_name1 = _ ( 'Simple login form' );
$this->_insertHTMLSection ( $section_name1 );
?>
<form id="login_form" action=<?php echo "'".selfURL()."' ";?>
method="post" style="position: relative">
<table
style="min-width:400px;margin-left: auto; margin-right: auto; <?php echo SIMPLELOGIN_LOGIN_BOX_BORDER;?>
">
<tr>
<td colspan="3"
style='text-align: center; font-weight: bold; font-size: 1.25em;'><?php echo $this->_login_title;?></td>
</tr>
<tr>
<td><label for="username"><?php _pesc('User name:');?></label></td>
<td><input type="text" name="username" id="username"
autocomplete="off"
onchange="pwd_change(document.getElementById('password').value);"
onkeydown="this.onchange()" onkeyup="this.onchange()"
<?php if($users_not_defined)printf(' value="%s" ',SIMPLELOGIN_DEMO_USERNAME);?>></td>
</tr>
<tr>
<td style="min-width: 93px"><label for="password"><?php _pesc('Password:');?></label></td>
<td style="min-width: 182px;"><input type="password" name="password"
id="password" onchange="pwd_change(this.value);" <?php if($users_not_defined)printf(' value="%s" ',SIMPLELOGIN_DEMO_PASSWORD);?>
onkeydown="this.onchange()" onkeyup="this.onchange()" style="<?php echo !$this->isSSL()?"background-color:red;":"";?>"><?php echo $this->getSSLIcon();?></td>
<td style="min-width: 91px;" id="td_pwd_strength"><span
style="padding-bottom: 1px; border-style: none none solid; border-width: medium medium thick; border-color: #c0c0c0"
id="pwd_strength"></span></td>
</tr>
<tr>
<td colspan="3" align="center"><input type="button" class="button"
id="btn_login"
onclick="document.getElementById('login_form').submit();"
value="<?php _pesc('Log in');?>"><a class='help' id='btn_login_hint'
style="display: none" onclick=<?php echo echoHelp($help);?>> [?]</a>
</tr>
<?php
if ($users_not_defined) {
?>
<tr>
<td colspan="3">&nbsp;</td>
</tr>
<tr>
<td colspan="3" class="highlight-box hintbox rounded-container"><?php printf(_esc('No user:password defined. Using the default %s username'),'<strong>'.SIMPLELOGIN_DEMO_USERNAME.'</strong>');?><a
class='help' onclick=<?php echo echoHelp($help_simplepwd);?>> [?]</a></td>
</tr>		
<?php
} else {
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
<?php printf("return jsMyBackup.popupError('%s','<b>%s</b> and <b>%s</b> %s');",_('Error'),_('Username'),_('Secret'),_('must not be empty'));?>
BlockUI.block(document.body);
document.body.style.cursor='wait';
jsMyBackup.asyncGetContent(url,"action=login_recovery&username="+username+"&secret="+secret,null,function(xhr){BlockUI.unblock(document.body);document.body.style.cursor='initial';});
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
pwd_change(document.getElementById('password').value);
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
$randomPassword = function ($len) {
$result = '';
for($i = 0; $i < $len; $i ++) {
$result .= chr ( rand ( 48, 122 ) );
}
return $result;
};
if ($email = $this->_pwd_manager->verifySecret ( $username, $secret )) {
$len = 8;
do {
$password = $randomPassword ( $len ++ );
} while ( $this->_passwordEntropy ( $password ) < SIMPLELOGIN_DEFAULT_PASSWORD_ENTROPY );
$this->_pwd_manager->setUserPassword ( $username, $password );
} else {
throw new MyException ( _ ( 'Bad username or secret' ) );
}
$body = 'You have requested to reset your password.' . PHP_EOL . sprintf ( _ ( "Your new randomly generated password is: %s" ), $password );
return @mail ( $email, sprintf ( _ ( '%s password recovery' ), WPMYBACKUP ), $body );
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