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
 * @file    : ftp-expert.php $
 * 
 * @id      : ftp-expert.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="ftpdirsep"><?php _pesc('Dir listing style');?></label></td>
<td><select name="ftpdirsep" id="ftpdirsep"
<?php echo $this->enabled_tag; ?> style='width: 100%'><option
value="u"
<?php
if ('u' == $this->_dirsep)
echo $selected;
?>><?php _pesc('Unix style');?></option>
<option value='w'
<?php
if ('w' == $this->_dirsep)
echo $selected;
?>><?php _pesc('Windows style');?></option></select></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_1 );
?>> [?]</a></td>
<td rowspan="5" style="vertical-align: top"><table
id="ftp_cainfo_table">
<tr>
<td><label for="ftp_ssl_ver"><?php _pesc('SSL version');?></label></td>
<td><select name="ftp_ssl_ver" id="ftp_ssl_ver"
<?php echo $this->enabled_tag;?> style="width: 100%">
<option value="<?php echo 0;?>"
<?php if(0==$ftp_ssl_ver) echo $selected;?>><?php _pesc('Let me choose');?></option>
<option value="<?php echo 1;?>"
<?php if(1==$ftp_ssl_ver) echo $selected;?>>TLS v1.x</option>
<option value="<?php echo 4;?>"
<?php if(4==$ftp_ssl_ver) echo $selected;?>>TLS v1.0</option>
<option value="<?php echo  5;?>"
<?php if( 5==$ftp_ssl_ver) echo $selected;?>>TLS v1.1</option>
<option value="<?php echo  6;?>"
<?php if( 6==$ftp_ssl_ver) echo $selected;?>>TLS v1.2</option>
<option value="<?php echo  2;?>"
<?php if( 2==$ftp_ssl_ver) echo $selected;?>>SSL v2</option>
<option value="<?php echo  3;?>"
<?php if( 3==$ftp_ssl_ver) echo $selected;?>>SSL v3</option>
</select></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_11 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="ftp_cainfo"><?php _esc('CA PEM path/file');?></label></td>
<td><input type="text" name="ftp_cainfo" id="ftp_cainfo"
style="width: 100%"
value="<?php echo $this->settings['ftp_cainfo'];?>"
<?php echo $this->enabled_tag;?>></td>
<td><a class='help' id='ftp_cainfo_help'
onclick=<?php
echoHelp ( $help_6 );
?>> [?]</a></td>
</tr>
<tr>
<td style="text-align: right"><input type="checkbox"
name="ftp_ssl_chk_peer" id="ftp_ssl_chk_peer" value="1"
<?php
echo true === strToBool ( $this->settings ['ftp_ssl_chk_peer'] ) ? 'checked' : '';
?>><input type="hidden" name="ftp_ssl_chk_peer" value="0"></td>
<td><label for="ftp_ssl_chk_peer"><?php _pesc('Check peers SSL identity');?></label></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_10 );
?>> [?]</a></td>
</tr>
<tr>
<td colspan="3" style="text-align: center"><a class="help"
onclick=<?php echoHelp($help_9);?>><?php _pesc('Why do we need all this stuff? How does SSL work?');?></a></td>
</tr>
</table></td>
</tr>
<tr>
<td><label for="ftpproto"><?php _pesc('Transport protocol');?></label></td>
<td><select name='ftpproto' id="ftpproto"
<?php echo $this->enabled_tag;?> style='width: 100%'
onchange='<?php echo isWin()?'jsMyBackup.validateSSLonWin(this);':'';?>;jsMyBackup.validateSSLCAInfo(this);'
onkeyup='this.onchange();'>
<option value="<?php echo CURLPROTO_FTP;?>"
<?php if (CURLPROTO_FTP==$this->_ftpproto) echo $selected;?>><?php _pesc('FTP');?></option>
<option value="<?php echo CURLPROTO_FTP|CURLPROTO_FTPS;?>"
<?php if ((CURLPROTO_FTP|CURLPROTO_FTPS)==$this->_ftpproto) echo $selected;?>><?php _pesc('FTP+SSL');?></option>
</select></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_2 );
?>> [?]</a></td>
</tr>
<tr>
<td><label for="ftp_lib"><?php _pesc('Transport library');?></label></td>
<td><select id="ftp_lib" name="ftp_lib"
<?php echo $this->enabled_tag;?> style='width: 100%'
onchange="var el=document.getElementById('ftpproto');jsMyBackup.validateSSLCAInfo(el);<?php echo isWin()?'jsMyBackup.validateSSLonWin(el);':'';?>"
onkeyup="this.onchange();">
<option value="curl"
<?php if('curl'==$this->settings['ftp_lib'])echo $selected;?>><?php _pesc('Curl library');?></option>
<option value="php"
<?php if('php'==$this->settings['ftp_lib'])echo $selected;?>><?php _pesc('PHP built-in FTP');?></option>
</select></td>
<td><a class='help' onclick=<?php
echoHelp ( $help_5 );
?>> [?]</a></td>
</tr>
<?php if(defined(__NAMESPACE__.'\\BANDWIDTH_THROTTLING')){?>
<tr>
<td><label for="ftp_throttle"><?php _pesc('Upload throttling');?></label></td>
<td><input type="number" name="ftp_throttle" id="ftp_throttle"
value="<?php echo $this->settings['ftp_throttle'];?>"
<?php echo $this->enabled_tag;?>> KiBps</td>
<td><a class='help' onclick=<?php
echoHelp ( $help_3 );
?>> [?]</a></td>
</tr>
<?php
}
if (defined ( __NAMESPACE__.'\\FILE_EXPLORER' )) {
?>
<tr>
<td><label for="ftp_direct_dwl"><?php _pesc('Direct download');?></label></td>
<td><input type="checkbox" name="ftp_direct_dwl" id="ftp_direct_dwl"
value='1'
<?php echo (isNull($this->settings,'ftp_direct_dwl',false)?'checked':'').' '.$this->enabled_tag;?>>
<input type='hidden' name='ftp_direct_dwl' value='0'><a class='help'
onclick=<?php echo echoHelp($help_4);?>> [?]</a><?php echo getSSLIcon();?></td>
</tr>
<?php }?>