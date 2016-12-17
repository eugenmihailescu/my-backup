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
 * @file    : SupportEditor.php $
 * 
 * @id      : SupportEditor.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class SupportEditor extends AbstractTargetEditor
{
private $_payed_version;
private $_is_activated;
private $_img_path;
private function _getContactTemplate($framed = true)
{
ob_start();
require_once $this->getTemplatePath('support-contact.php');
$template = ob_get_contents();
ob_end_clean();
return $framed ? $this->insertEditorTemplate('Contact', $template, null, true) : $template;
}
private function _getJavaScripts()
{
if (! $this->_is_activated)
return;
$this->java_scripts[] = 'parent.php_setup=function(){parent.asyncRunJob(parent.ajaxurl,"' . http_build_query(array(
'action' => 'php_setup',
'nonce' => wp_create_nonce_wrapper('php_setup')
)) . '","PHP Setup",null, null, 4, null, -1,null,false);}';
ob_start();
?>
parent.html2text = function (string) {
result = string.replace(/<([^\s]+?)[^\>]*>/g, '|');
result = result.replace(/(\|\|):\1/g, ':');
result = result.replace(/\|{4}/g, '\n');
return result;
};
parent.copy2clpb=function(sender){
var altcaption=sender.getAttribute('data-altcaption');
sender.setAttribute('data-altcaption',sender.value);
sender.value=altcaption;
var groups=['system','phpext','wpplugins'],i,j,el,str='';
for(i=0;groups.length>i;i+=1){
el=document.querySelectorAll('.check-setup-'+groups[i]);
if(parent.globals.UNDEFINED!=el)
for(j=0;el.length>j;j+=1)
{
str+=parent.html2text(el[j].outerHTML);
}
}
el=document.querySelectorAll('.check-setup-wrapper div');
if(parent.globals.UNDEFINED!=el && el.length){
el[0].style.display=''==el[0].style.display || 'inherit'==el[0].style.display?'none':'inherit';
el=document.querySelectorAll('.check-setup-wrapper textarea');
if(parent.globals.UNDEFINED!=el && el.length){
el[0].value=str;
el[0].style.display=''==el[0].style.display||'none'==el[0].style.display?'inherit':'none';
if('none'!=el[0].style.display){
el[0].select();
el[0].scrollTop=0;
}
}
}
};			
<?php
$this->java_scripts_load[] = ob_get_clean();
$this->_getExpertJavaScripts();
}
private function _getExpertJavaScripts()
{
$this->java_scripts[] = "parent.print_debug_sample=function(type){parent.asyncGetContent(parent.ajaxurl,'action=print_debug_sample&type='+type+'&nonce=" . wp_create_nonce_wrapper('print_debug_sample') . "',parent.dummy,function(xmlhttp){jsMyBackup.popupWindow('Sample '+type+' log','<div class=\'cui-console\'><pre>'+xmlhttp.responseText+'</pre></div>');});}";
$this->java_scripts_load[] = 'parent.toggle_header("support_expert_box");'; 
}
protected function initTarget()
{
parent::initTarget();
$this->_img_path = dirname(__DIR__);
($this->_is_activated = check_is_activated()) && $this->_getJavaScripts();
$this->inBetweenContent = $this->_is_activated ? $this->_getContactTemplate() : '';
$this->_payed_version = false;
if (! $this->_is_activated)
$this->customTitle = 'Contact'; 
$this->_getJavaScripts();
}
protected function hideEditorContent()
{
return ! $this->_is_activated;
}
protected function getEditorTemplate()
{
global $TARGET_NAMES;
if (! $this->_is_activated) {
echo $this->_getContactTemplate(false);
return;
}
$support_addon = TEMPLATES_PATH . 'support-addon.php';
_file_exists($support_addon) && include_once $support_addon;
$help_3 = "'" . _esc('This will check your system to detect any missing or miss-configured extension.') . "'";
$php_min_ver = '<a class="help" onclick=' . getHelpCall("'" . sprintf(_esc("Please don`t bother using any version older than PHP %s. Their %s occurred like %d years ago, are very,very buggy and they have lots of security holes. That`s why I don`t bother writting software for anything else than PHP %s or newer :-)<br>Make sure your web hosting company provides you at least the version %s (which btw ended also like %.1f years ago)."), SUPPORT_MIN_PHP, getAnchorE('End-Of-Life', PHP_HOME_URL . 'eol.php'), time() / 31536000 - 41.03, SUPPORT_MIN_PHP, SUPPORT_MIN_PHP, time() / 31536000 - 44.7) . "'") . '>PHP ' . SUPPORT_MIN_PHP . '+</a>';
$wp_min_ver = is_wp() ? (' ' . _esc('and') . ' ' . '<a class="help" onclick=' . getHelpCall("'" . sprintf(_esc('This application was tested and works with all version of WordPress 3.0 to %s.<br>%s the majority of WordPress installations (ie. 99.9%%) runs on WordPress version 3.0 or newer. The same source states that 90%% of these installations run on PHP 5.3 or newer.<br>We expect that this application will install and run without problems on any of these versions.'), get_bloginfo('version', 'display'), getAnchorE('According to wordpress.org', 'https://wordpress.org/about/stats')) . "'") . '>WordPress ' . SUPPORT_MIN_WP . '+</a>') : '';
$iis_apache_php = sprintf(_esc('over IIS/Apache with MySQL v%s+, %s%s'), SUPPORT_MIN_MYSQL, $php_min_ver, $wp_min_ver);
require_once $this->getTemplatePath('support.php');
}
protected function getExpertEditorTemplate()
{
if (! $this->_is_activated)
return;
$help_debug_on = "'" . _esc('This option activates a mechanism by which when an error occurs (no matter if it`s an program error or just an exception, like I/O error, HTTP timeout, etc) its trace is automatically captured and saved into a special trace log file. <a class=\\\'help\\\' href=\\\'#\\\' onclick=\\\'jsMyBackup.print_debug_sample(&quot;debug&quot;);\\\'>' . _esc('See example') . '</a>.');
$help_debug_on .= _esc("<p style=\'font-weight:bold\'>OK, but what has this to do with me?</p>Well, it has everything to do with you when you`ll got an error and you need support. If you come to me empty-handed I will return to you empty-handed.<br>");
$help_debug_on .= _esc("So you see, the easiest way to fix a problem is to gather as much technical data from you as possible. Not that much, though. I <i>a priori</i> assume that not everyone of you are techyes so my jobs is to make your life easier when comes to `debugging`. It would be hard for someone that is neither programmer or sysadmin to understand the complexity behind the software, it is really complex even for us who make a life from it.<br>Back to the topic, this `debug` option captures the last function calls just before the error|exception occurred. It will point me in the right source file at the exact line of code that triggered that error. From there to the `hotfix` is just a matter of hours.<br>So I will not aggress you with techy questions, you won`t spend your time by making a ping-pong with the support team, it`s a win-win situation.<br>I`ve spent many years in the first line of technical support and hotline so it is supposed that I know what I am talking about :-)<p style=\'font-weight:bold\'>When shouldI activate this option? Always? Never?</p>Well, basically you won`t need to activate this right now. Anyway when a problem occurs and you think it may be a software malfunction (because anytime you run a specific job you get exactly the same error, no matter what) you could just turn ON this option, try to repeat whatever you did before until you get the same error then submit a bug report by choosing to include (see <i>Attach system info</i>) also the debug information. Then you may turn off this option, there is no good to keep it activated all the time. But if you keep it for let`s say half year it will capture (in a trace log file) all those errors you`ve got so far so sending to me a half year error log file won`t help either.") . "'";
$help_curl_debug_on = sprintf(_esc('This option allows (mostly you) to understand how works the communication between your webserver (where %s is installed) and the other server over Internet (like Google Cloud Storage server or Dropbox server, etc). Depending on your webserver system configuration there may occur or not (hopefully not) all kind of situations that no programmer in the world could anticipate. So this option will capture in a special log file all the Curl commands (but NOT the data, thank God! - <a class=\\\'help\\\' href=\\\'#\\\' onclick=\\\'jsMyBackup.print_debug_sample(&quot;curl&quot;);\\\'>see example</a>) like <i>read the file list</i>, <i>download/upload this file</i>, etc. If there is a problem related to communication between %s and some Internet server (eg. Google`s, Dropbox, FTP, etc) you might take a look at the Curl logginf file or if you think it`s due to a software malfunction just send a bug report by choosing to include (see <i>Attach system info</i>) also the Curl debug information.<br>'), WPMYBACKUP, WPMYBACKUP);
$help_curl_debug_on .= sprintf(_esc("Oh btw, if you wonder what the heck is Curl, %s is just a software library (written by %s and other - thanks Daniel+others!) that helped me to write the code that takes care of sending and receiving data over Internet, like uploading a backup to Dropbox and so on. It is %s that there is no mistery why it become so popular among the PHP community and not only."), getAnchorE('Curl', 'http://en.wikipedia.org/wiki/CURL'), getAnchorE('Daniel Stenberg', 'http://daniel.haxx.se'), getAnchorE('so powerful', 'http://curl.haxx.se/docs/comparison-table.html'));
$CurlErrorCode = _esc('libCURL Error Codes');
$help_curl_debug_on .= '<p style=\\\'font-weight:bold\\\'>' . $CurlErrorCode . '</p>' . sprintf(_esc('Whenever you find a CURL error which code you do not understand please check the %s.'), getAnchorE($CurlErrorCode, 'http://curl.haxx.se/libcurl/c/libcurl-errors.html'));
$help_curl_debug_on .= '<p style=\\\'font-weight:bold\\\'>' . sprintf(_esc('Let`s make the long story short</p>Activate this option (likewise Debug ON) either when you want to report a problem OR when you want to dive deeper into %s and/or protocol debugging and understanding :-)'), getAnchorE('libcurl', 'http://curl.haxx.se/libcurl'));
$help_curl_debug_on = "'$help_curl_debug_on '";
$help_yayui_on = getAnchorE('YAYUI', 'http://yayui.mynixworld.info') . sprintf(_esc('%s is a small library (I wrote a while ago) that enhances the page loading speed by reducing the page length just before the page is dispatched from your webserver towards your web browser. It strips the unnecessary whitespaces, comments and even obfuscates the JavaScript code making it shorter thus `faster`. The only benefit of this option would be that %s administrative pages will load faster and will drain less RAM from your local system.<br>'), '(Yet Another YUI compressor)', WPMYBACKUP);
$help_yayui_on .= _esc('If you`re in doubt then leave this option off. It won`t harm either way.');
$help_yayui_on = "'$help_yayui_on '";
$help_debug_statusbar_on = "'" . _esc('This option will display a status bar that will stick at the bottom of the page. It will display how long took the webserver to produce the page, how many bytes were received by browser, how much did the YAYUI reduced the content size, how long took the browser to render the document and to load all the remote resources. It also display the total time from the moment the server received the request till the moment you saw the page on screen.') . "'";
$help_stats_debug_on = "'" . _esc('This option will dump to a log file the JavaScript code that represents the datase sent to Google Charts API in order to get back a user-friendly chart.') . '<br><a class=\\\'help\\\' href=\\\'#\\\' onclick=\\\'jsMyBackup.print_debug_sample(&quot;stats&quot;);\\\'>' . _esc('See example') . ".</a>'";
$help_smtp_debug_on = "'" . _esc('This option will allow you to track the SMTP mail communication.') . ' ' . readMoreHereE('https://pear.php.net/manual/en/package.networking.net-smtp.net-smtp.setdebug.php') . "'";
$help_restore_debug_on = "'" . _esc('This option will allow you to trace the failed restore operations.') . "'";
$help_cookie_accept_on = "'" . _esc('This option allows you to set on|off the usage of cookies, despite of what you have already answered when/if the cookie warning banner had popped-up earlier. So when you don`t accept cookies some UI options (eg. <i>Expert settings</i> minimize status) do not remember their last state (they do remember if they are feed with cookies, that`s why the cookies were invented! I really miss my mum, she knows everything about cookies ;-).<br><br>We don`t use cookie to track information about you but we would like to use some to improve the user experience. So finally it`s your call!') . "'";
$help_wp_debug_on = "'" . sprintf(_esc('Set this option if you want to enable the display of notices during debugging.<br>By setting this option ON you should expect some notices and warning messages to be displayed. They may come from our plugin, from somebody else plugin or even from the WordPress itself. That`s perfectly fine.<br>%s.'), readMoreHereE('https://codex.wordpress.org/WP_DEBUG')) . "'";
$help_whitespace = "'" . _esc('Specify the minimum interval in minutes the dashboard will check and notify you if, when attempting to download a file via dashboard (a SQL script, a remote file), the downloaded file seems corrupted (cannot be opened) because some extra whitespaces are inserted in the file`s header.') . ' ' . readMoreHereE(APP_PLUGIN_FAQ_URI . '#q7') . '.<br>' . _esc('When set to 0 (zero) this option is regarded as disabled.') . "'";
$options = array(
'debug' => _esc('Debug trace ON'),
'curl_debug' => _esc('Curl debug ON'),
'yayui' => _esc('Yayui optimize ON'),
'stats_debug' => _esc('Statistics debug ON'),
'smtp_debug' => _esc('SMTP debug ON'),
'restore_debug' => _esc('Restore debug ON'),
'debug_statusbar' => _esc('Debug statusbar'),
'cookie_accept' => _esc('Accept cookies')
);
is_wp() && $options['wp_debug'] = 'Set WP_DEBUG=true';
$tr = array();
$i = 0;
foreach ($options as $op => $op_name) {
$help = 'help_' . $op . '_on';
$tr[] = '<tr><td ' . (0 == $i ++ ? 'style="width:0px"' : '') . '><label for="' . $op . '_on">' . $op_name . '</label></td><td><input type="checkbox" name="' . $op . '_on" id="' . $op . '_on" value="1" ' . (strToBool($this->settings[$op . '_on']) ? 'checked' : '') . '><input type="hidden" name="' . $op . '_on" value="0"><a class="help" onclick=' . getHelpCall($$help) . '> [?]</a></td></tr>';
}
echo '<tr><td colspan="2">' . _esc('This is a complex application. Sometimes shits just happens and thus it is always a good idea to have a first aid kit nearby.') . '</td></tr>';
echo implode(PHP_EOL, $tr);
require_once $this->getTemplatePath('support-expert.php');
}
}
?>