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
 * @file    : backup-settings.php $
 * 
 * @id      : backup-settings.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="http_proxy"><?php _pesc('HTTP proxy');?></label></td>
<td><input type="text" name="http_proxy" id="http_proxy"
value="<?php echo $this->settings['http_proxy'];?>" style="width: 100%"></td>
<td><label for="http_proxy_port"><?php _pesc('Port');?></label></td>
<td><input type="number" name="http_proxy_port" id="http_proxy_port"
value="<?php echo $this->settings['http_proxy_port'];?>" style="width: 100%"
min="20" max="65535" size="5"></td>
<td><a class='help' onclick=<?php
echoHelp( $help_4 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="http_proxy_user"><?php _pesc('Proxy user');?></label></td>
<td><input type="text" name="http_proxy_user" id="http_proxy_user"
value="<?php echo $this->settings['http_proxy_user'];?>" style="width: 100%"></td>
<td><label for="http_proxy_pwd"><?php _pesc('Pwd');?></label></td>
<td><input type="password" name="http_proxy_pwd" id="http_proxy_pwd"
value="<?php echo $this->settings['http_proxy_pwd']; if(!(isSSL()||empty($http_proxy_pwd))) echo " style='background-color:#FF2C00;'";?>"><?php echo getSSLIcon();?></td>
<td><a class='help' onclick=<?php
echoHelp( $help_5 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="http_proxy_auth"><?php _pesc('Proxy auth method');?></label></td>
<td><select name="http_proxy_auth" id="http_proxy_auth"><option
value="<?php echo CURLAUTH_BASIC;?>"
<?php if(CURLAUTH_BASIC==$this->settings['http_proxy_auth'])echo $selected;?>>Basic</option>
<option value="<?php echo CURLAUTH_NTLM;?>"
<?php if(CURLAUTH_NTLM==$this->settings['http_proxy_auth'])echo $selected;?>>NTLM</option></select><a
class='help' onclick=<?php
echoHelp( $help_6 );
?>> [?]</a></td>
<td><label for="http_proxy_type"><?php _pesc('Type');?></label></td>
<td><select name="http_proxy_type" id="http_proxy_type" style="width: 100%"><option
value="<?php echo CURLPROXY_HTTP;?>"
<?php if(CURLPROXY_HTTP==$this->settings['http_proxy_type'])echo $selected;?>>HTTP
proxy</option>
<option value="<?php echo CURLPROXY_SOCKS5;?>"
<?php if(CURLPROXY_SOCKS5==$this->settings['http_proxy_type'])echo $selected;?>>Socks5
proxy</option></select></td>
<td><a class='help' onclick=<?php
echoHelp( $help_7 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="ssl_cainfo"><?php _pesc('SSL CA file/path');?></label></td>
<td><input type="text" name="ssl_cainfo" id="ssl_cainfo"
value="<?php echo $this->settings['ssl_cainfo'];?>"><a class='help'
onclick=<?php
echoHelp( $help_1 );
?>> [?]</a></td>
<td style="text-align: center"><input type="checkbox" name="ssl_chk_peer"
id="ssl_chk_peer"
<?php
echo true === strToBool( $this->settings['ssl_chk_peer'] ) ? 'checked' : '';
?>
value="1"><input type="hidden" name="ssl_chk_peer" value="0"></td>
<td><label for="ssl_chk_peer"><?php _pesc('Check peers SSL identity');?></label></td>
<td><a class='help' onclick=<?php
echoHelp( $help_8 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="ssl_ver"><?php _pesc('SSL version');?></label></td>
<td><select name="ssl_ver" id="ssl_ver" style="width: 100%">
<option value="<?php echo 0;?>"
<?php if(0==$this->settings['ssl_ver']) echo $selected;?>>Let me choose</option>
<option value="<?php echo 1;?>"
<?php if(1==$this->settings['ssl_ver']) echo $selected;?>>TLS v1.x</option>
<option value="<?php echo 4;?>"
<?php if(4==$this->settings['ssl_ver']) echo $selected;?>>TLS v1.0</option>
<option value="<?php echo  5;?>"
<?php if( 5==$this->settings['ssl_ver']) echo $selected;?>>TLS v1.1</option>
<option value="<?php echo  6;?>"
<?php if( 6==$this->settings['ssl_ver']) echo $selected;?>>TLS v1.2</option>
<option value="<?php echo  2;?>"
<?php if( 2==$this->settings['ssl_ver']) echo $selected;?>>SSL v2</option>
<option value="<?php echo  3;?>"
<?php if( 3==$this->settings['ssl_ver']) echo $selected;?>>SSL v3</option>
</select><a class='help' onclick=<?php
echoHelp( $help_9 );
?>> [?]</a></td>
<td style="text-align: center"><input type="checkbox" name="ssl_chk_host"
id="ssl_chk_host"
<?php
echo true === strToBool( $this->settings['ssl_chk_host'] ) ? 'checked' : '';
?>
value="1"><input type="hidden" name="ssl_chk_host" value="0"></td>
<td><label for="ssl_chk_host"><?php _pesc('Check host SSL identity');?></label></td>
<td><a class='help' onclick=<?php
echoHelp( $help_10 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="dwl_throttle"><?php _pesc('Download throttling');?></label></td>
<td colspan="3"><input type="number" name="dwl_throttle" id="dwl_throttle"
value="<?php echo $this->settings['dwl_throttle'];?>" style="width: 70px">
KiBps <a class='help' onclick=<?php
echoHelp( $help_2 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="request_timeout"><?php _pesc('Request timeout');?></label></td>
<td colspan="3"><input type="number" name="request_timeout"
id="request_timeout" style="width: 70px"
value="<?php echo $this->settings['request_timeout'];?>"> sec<a class='help'
onclick=<?php
echoHelp( $help_3 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="netif_out"><?php _pesc('Outgoing If name');?></label></td>
<td colspan="3"><input type="text" name="netif_out" id="netif_out"
value="<?php echo$this->settings['netif_out'];?>"><a class='help'
onclick=<?php
echoHelp( $help_11 );
?>> [?]</a></td>
</tr>