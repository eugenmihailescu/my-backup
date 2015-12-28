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
 * @version : 0.2.2-10 $
 * @commit  : dd80d40c9c5cb45f5eda75d6213c678f0618cdf8 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Dec 28 17:57:55 2015 +0100 $
 * @file    : backupjob.php $
 * 
 * @id      : backupjob.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<tr>
<td><label for="name"><?php _pesc('Backup name');?></label></td>
<td><input type="text" name="name" id='name'
value=<?php echo '"' . $this->settings['name'] . '" '; ?>
onchange="js56816a36b58dc.toggle_backup_name(this);" onkeypress="this.onchange();"
size="30"><a class='help' onclick=<?php echo echoHelp($help_1); ?>>[?]</a></td>
</tr>
<tr>
<td><label for="url"><?php _pesc('Backup prefix');?></label></td>
<td><input type="text" name="url" id='url'
value=<?php echo '"' . $this->settings['url'] . '" '; ?>
onchange="js56816a36b58dc.toggle_backup_name(this);" onkeypress="this.onchange();"
size="30"><a class='help' onclick=<?php echo echoHelp($help_2); ?>>[?]</a></td>
</tr>
<tr>
<?php
if ( is_multisite_wrapper() ) {
$wrkdir_name = 'blog_wrkdir';
$wrkdir_prefix = $wpmu_dir_url;
} else {
$wrkdir_name = 'wrkdir';
$wrkdir_prefix = '';
}
?>
<td><label for="<?php echo $wrkdir_name;?>"><?php _pesc('Working directory');?></label></td>
<td>
<?php echo $wrkdir_prefix;?>
<input type="text" name="<?php echo $wrkdir_name;?>"
id="<?php echo $wrkdir_name;?>"
value="<?php echo $this->settings[$wrkdir_name] ; ?>" size="30"
<?php echo $this->enabled_tag;?>><a class='help'
onclick=<?php
echoHelp( $help_3 );
?>>[?]</a>
</td>
</tr>
<tr>
<td><label for="compression_type"><?php _pesc('Compression type');?></label></td>
<td><table class="form-settings" style=''>
<tr>
<td><select name="compression_type" id='compression_type'
onchange="js56816a36b58dc.toggle_compression_type(this);">
<?php echo $compression_ext;?>
</select><a class='help' onclick=<?php echo echoHelp($help_6); ?>>[?]</a></td>
<td><label for="compression_level"><?php _pesc('Level');?></label></td>
<td><select name="compression_level" id="compression_level"
onchange="js56816a36b58dc.submitOptions(this,0);" style='width: 100%'>
<?php echo $clo_str;?>
</select></td>
</tr>
</table></td>
</tr>
<tr>
<td><label for="toolchain_grp"><?php _pesc('Compression tool');?></label></td>
<td><table id="toolchain_grp">
<tr>
<td><input type="radio" name="toolchain" id="toolchain_int" value="intern"
<?php echo $intern_checked; ?> onclick="js56816a36b58dc.submitOptions(this,0);"><label
for="toolchain_int"><?php echo WPMYBACKUP; ?></label></td>
<td><?php if(defined(__NAMESPACE__.'\\OPER_COMPRESS_EXTERN')){?>
<table>
<tr>
<td><input type="radio" name="toolchain" id="toolchain_ext"
value="extern" <?php echo $extern_checked . $extern_enabled; ?>
onclick="js56816a36b58dc.submitOptions(this,0);">
<?php
if ( isWin() ) {
?><a class='help' onclick=<?php echo echoHelp ( $help_7 ); ?>>[?]</a><?php
}
echo "<label for='toolchain_ext'>" . sprintf( _esc( 'Local %s' ), PHP_OS ) . "</label>";
?>
</td>
<td>
<?php
if ( $extern_enabled && ! $extern_toolchain_status )
printf( 
'<img src="%s" title="%s">', 
$this->getImgURL( 'unchecked.png' ), 
sprintf( _esc( 'External toolchain doesn`t seem to work on local %s' ), PHP_OS ) );
?>
</td>
</tr>
</table>
<?php
}
?></td>
<?php
if ( defined( __NAMESPACE__.'\\JOB_BENCHMARK' ) && $os_tool_ok ) {
?><td><input type="button" class="button"
value="&nbsp;&nbsp;&nbsp;<?php _pesc('Benchmark');?>" id="btn_benchmark"
title="<?php _pesc('Run a toolchain benchmark test now');?>"
<?php if(0==feature_is_licensed('benchmark',$this->license[$this->license_id]))echo 'disabled';?>
onclick="<?php
echo "js56816a36b58dc.popupConfirm('" . _esc( 'Choose what to test' ) . "','" . sprintf( 
_esc( 
"This will create a %s random file and will try to use both (<b>%s</b> and <b>%s</b>) compression tools to measure/compair their performance. Its aim is to assist you deciding which tool to use and why.<br>On the other hand perhaps you might want to test some real-life data, namely those files you selected on the <b>Backup source</b> tab. So what is going to be?" ), 
getHumanReadableSize( BENCHMARK_FILE_SIZE * MB ), 
WPMYBACKUP, 
PHP_OS ) . "',null,{'" . _esc( 'Random file' ) . "':'js56816a36b58dc.removePopupLast();js56816a36b58dc.do_benchmark(1);','" .
_esc( 'My files' ) . "':'js56816a36b58dc.removePopupLast();js56816a36b58dc.do_benchmark(0);','" . _esc( 'Cancel' ) . "':null});";
?>"></td>
<td><a class="help" onclick=<?php
echo echoHelp( $help_8 );
?>> [?]</a></td><?php } ?></tr>
</table></td>
</tr>
<?php
if ( defined( __NAMESPACE__.'\\OPER_COMPRESS_EXTERN' ) && 'extern' == $compression_tool ) {
?>
<tr>
<td class='caption'><label for="hintbox <?php echo $this->container_shape;?>"><?php printf( _esc('%s command'),PHP_OS); ?></label></td>
<td><table>
<tr>
<td>
<div class='hintbox <?php echo $this->container_shape;?>'
id="comp_cmd_hint"
onmouseover="js56816a36b58dc.showClipboardBtn(this,'visible','comp_cmd_clpb');"
onmouseout="js56816a36b58dc.showClipboardBtn(this,'hidden','comp_cmd_clpb');">
<?php !$os_tool_ok && print('<p style="color:red">'._esc('The following command doesn`t work on your local system. Hint: check if your PHP has the necessary execute permission (ie. copy and execute the command below at the command prompt).').'</p>');echo $compress_cmd;?></div>
<img id="comp_cmd_clpb"
src="<?php echo $this->getImgURL ( 'edit-copy-32.png' ) ;?>"
style="position: relative; float: right; right: 5px; visibility: hidden; cursor: pointer;"
onmouseover="this.style.visibility='visible'"
onclick="js56816a36b58dc.popupPrompt('<?php _pesc('Compatibility-mode copy');?>','<?php _pesc('Copy to clipboard: Ctrl+C, ESC (will strip the HTML tags :-)');?>', null,{'<?php _pesc('Close (ESC)');?>':null},js56816a36b58dc.stripHelpLink('comp_cmd_hint'),'textarea');"
title='<?php _pesc('Click to copy to clipboard');?>'>
</td>
<td><img
src=<?php echo '"' . $this->getImgURL($os_tool_ok?'check.png':'unchecked.png') . '"'; ?>
alt="checked"
title=<?php
printf( 
"'" . _esc( "This confirms that the command has been tested and %s on %s" ) . "'", 
$os_tool_ok ? 'works' : 'dont`t work', 
PHP_OS );
?> /></td>
</tr>
</table></td>
</tr>
<?php
}
?>
<tr>
<td><label for="size"><?php _pesc('Media spanning');?></label></td>
<td><input type="number" name="size" id="size"
value=<?php echo '"' . $this->settings['size'] . '"'; ?> min="0"> MiB<a
class='help' onclick=<?php
echoHelp( $help_4 );
?>>[?]</a></td>
</tr>
<tr>
<td><label for="verbose"><?php _pesc('Verbosity');?></label></td>
<td><select name="verbose" id="verbose">
<?php echo $o_str; ?>
</select><a class='help' onclick=<?php echoHelp ( $help_9 );?>>[?]</a></td>
</tr>
<tr>
<td><label for="email"><?php _pesc('E-mail notification');?></label></td>
<td><input type="email" id="email" name="email"
value=<?php echo '"' . $this->settings['email'] . '"'; ?> size="30"
style='width: 90%'><a class='help' onclick=<?php
echoHelp( $help_5 );
?>>[?]</a></td>
</tr>
<tr>
<td colspan="2">
<table>
<tr>
<td><input type="button" name='run_wpmybackup_backup' class="button"
value="&nbsp;&nbsp;&nbsp;<?php _pesc('Run Backup Now');?>"
id="btn_run_backup"
onclick=<?php echo '"js56816a36b58dc.asyncRunBackup(\'run_backup\',\''._esc('Backup').'\',\''.wp_create_nonce_wrapper('run_backup').'\',\''.wp_create_nonce_wrapper('get_progress').'\',\''.wp_create_nonce_wrapper('cleanup_progress').'\',\''.wp_create_nonce_wrapper('abort_job').'\');" ';?>
title='<?php _pesc('Click to run the backup now. It may take a while..');?>'></td>
<td><input type="hidden" name="run_backup" value="0"></td>
<td><div class="spin" id="spin_run"></div></td>
<td><div class="spin_hint" id="hint_run"><?php _pesc('Please wait while backup is running...');?></div></td>
</tr>
</table>
</td>
</tr>