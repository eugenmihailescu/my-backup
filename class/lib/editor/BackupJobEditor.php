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
 * @version : 0.2.3-3 $
 * @commit  : 961115f51b7b32dcbd4a8853000e4f8cc9216bdf $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 16 15:27:30 2016 +0100 $
 * @file    : BackupJobEditor.php $
 * 
 * @id      : BackupJobEditor.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class BackupJobEditor extends AbstractTargetEditor {
private function _getJavaScripts() {
global $COMPRESSION_LEVEL_SUPPORT, $PROGRESS_PROVIDER, $registered_ciphres;
$level_support = "var can_level,support_level=[],e=document.getElementById('compression_level');";
foreach ( $COMPRESSION_LEVEL_SUPPORT as $format => $support )
$support && $level_support .= sprintf( 'support_level[%d]=%s;', $format, $support );
$this->java_scripts[] = "parent.toggle_compression_type=function(sender) {" . $level_support .
"can_level='undefined'!=typeof support_level[sender.options[sender.selectedIndex].value];if(!can_level)e.value = '0';e.disabled = !can_level;}";
$this->java_scripts[] = "parent.toggle_compression_type(document.getElementById('compression_type'));";
$this->java_scripts[] = "parent.toggle_backup_name(document.getElementById('name'));";
$this->java_scripts[] = "parent.toggle_backup_name(document.getElementById('url'));";
if ( defined( __NAMESPACE__.'\\JOB_BENCHMARK' ) )
$this->java_scripts[] = "parent.do_benchmark=function(opt){parent.asyncRunBenchmark('compression_benchmark&type='+(2-opt),'" .
_esc( 'Benchmark' ) . "','" . wp_create_nonce_wrapper( 'compression_benchmark' ) . "','" .
wp_create_nonce_wrapper( 'get_progress' ) . "','" . wp_create_nonce_wrapper( 'cleanup_progress' ) .
"','" . wp_create_nonce_wrapper( 'abort_job' ) . "');};";
if ( ! empty( $registered_ciphres ) && isset( $this->settings['encryption'] ) &&
! empty( $this->settings['encryption'] ) ) {
$this->java_scripts[] = 'parent.decrypt_action=function(action,nonce){document.getElementById(parent.globals.ADMIN_FORM).setAttribute("action",jsMyBackup.ajaxurl);document.getElementsByName("action")[0].value=action;document.getElementsByName("nonce")[0].value=nonce;};';
$this->java_scripts[] = "parent.do_decrypt=function(){var b=document.getElementById('do_decrypt'),e=document.getElementById('decrypt_file');e.onchange=function(){b.className='button-primary';b.style.display='inline-block';b.value='" .
_esc( 'Decrypt Now' ) .
"';};if('none'==e.style.display){e.style.display='inline-block';b.style.display='none';}else{parent.decrypt_action('decrypt_file','" .
wp_create_nonce_wrapper( 'decrypt_file' ) .
"');document.getElementById('wpmybackup_admin_form').submit();}}";
}
$this->java_scripts[] = getBackupSourcesJS( $PROGRESS_PROVIDER );
}
protected function initTarget() {
parent::initTarget();
$this->enabled_tag = is_multisite_wrapper() ? 'disabled' : '';
$this->_getJavaScripts();
}
protected function getEditorTemplate() {
global $COMPRESSION_NAMES, $COMPRESSION_LIBS, $VERBOSITY_MODES, $exclude_files_factory;
$excl_files = explode( ',', $this->settings['excludefiles'] );
foreach ( $excl_files as $key => $value )
if ( in_array( $value, $exclude_files_factory ) )
$excl_files[$key] = @constant( __NAMESPACE__ . '\\' . substr( $value, 1, strlen( $value ) - 2 ) );
$compression_tool = $this->settings['toolchain'];
$extern_enabled = defined( __NAMESPACE__.'\\OPER_COMPRESS_EXTERN' );
$extern_toolchain_status = testOSTools( 
$this->settings['wrkdir'], 
$this->settings['compression_type'], 
$this->settings['compression_level'], 
$this->settings['size'], 
$excl_files, 
explode( ',', $this->settings['excludedirs'] ), 
explode( ',', $this->settings['excludeext'] ), 
$this->settings['bzipver'], 
$this->settings['cygwin'] );
$os_tool_ok = $extern_enabled && $extern_toolchain_status;
$os_tool_ok = true;
$extern_enabled = $os_tool_ok ? '' : ' disabled';
if ( ! $os_tool_ok ) {
$this->settings['toolchain'] = 'intern';
update_option_wrapper( WPMYBACKUP_OPTION_NAME, $this->settings );
}
if ( 'extern' == $compression_tool )
$compress_cmd = getTarNZipCmd( 
$this->settings['dir'], 
addTrailingSlash( $this->settings['wrkdir'] ) .
( '' == $this->settings['name'] ? $this->settings['url'] . "-yymmdd-hhiiss" : $this->settings['name'] ), 
$this->settings['compression_type'], 
$this->settings['compression_level'], 
MB * $this->settings['size'], 
$excl_files, 
explode( ',', $this->settings['excludedirs'] ), 
explode( ',', $this->settings['excludeext'] ), 
$this->settings['bzipver'], 
$this->settings['cygwin'] );
$intern_checked = ( 'intern' == $compression_tool || ! $os_tool_ok ) ? 'checked' : '';
$extern_checked = ( 'extern' == $compression_tool && $os_tool_ok ) ? 'checked' : '';
$o_str = '';
foreach ( $VERBOSITY_MODES as $o => $o_desc )
$o_str .= sprintf( 
'<option value="%s" %s>%s</option>', 
$o, 
$o == $this->settings['verbose'] ? 'selected' : '', 
$o_desc );
$comp_level_opts = array( _esc( 'None' ), _esc( 'Best speed' ) );
for ( $i = 2; $i < 9; $i++ )
$comp_level_opts[] = $i;
$comp_level_opts[] = _esc( 'Best compression' );
$clo_str = '';
foreach ( $comp_level_opts as $level => $o )
$clo_str .= sprintf( 
'<option value="%d" %s>%s</option>', 
$level, 
$level == $this->settings['compression_level'] ? 'selected' : '', 
$o );
$loaded_extensions = get_loaded_extensions();
$compression_ext = '';
foreach ( $COMPRESSION_NAMES as $key => $value )
if ( ! isset( $COMPRESSION_LIBS[$key] ) || in_array( $COMPRESSION_LIBS[$key], $loaded_extensions ) )
$compression_ext .= sprintf( 
'<option value="%s"%s>%s</option>', 
$key, 
$key == $this->settings['compression_type'] ? ' selected' : '', 
strtoupper( $value ) );
if ( is_wp() ) {
$wpmu_dir = addTrailingSlash( 
get_site_option( 'wpmu_wrkdir', sys_get_temp_dir() . DIRECTORY_SEPARATOR . WPMYBACKUP_LOGS ) );
$wpmu_dir_url = getAnchor( $wpmu_dir, network_admin_url( 'settings.php' ) . '#' . WPMYBACKUP_LOGS );
}
$help_1 = "'" .
_esc( 
'The name of the backup file. When this is not defined the <b>Backup prefix</b><br>is used instead. When prefix is used this is disabled.<br><br>You may want to use this field when you want to use the same name for your<br>backup archive each time. If you want a dynamic name that contains also a<br>timestamp then leave this field empty.' ) .
"'";
$help_2 = "'" . sprintf( 
_esc( 
'When <b>Backup name</b> is not specified then this <b>Backup prefix</b> is used instead.<br>It`s pattern looks like this: <b>\' + document.getElementById(\'url\').value + \'</b>-%s<br>When <b>Backup name</b> field is used then this field is disabled.<br><br>You may want to use this field when you need a dynamic backup name ie. a<br>unique name for each backup archive (that ends with a %s).' ), 
getSpanE( 'YYMMDD-HHMMSS', 'brown' ), 
getSpanE( 'timestamp', 'brown' ) ) . "'";
$help_3 = _esc( 
'The path where the temporary files (like the temporary backup file) will be created.<br>Make sure that WordPress has read-write access and there is enough disk space.' );
is_multisite_wrapper() && $help_3 .= '<br>' . sprintf( 
_esc( 
'On multisite installation this location is set automatically. The network administrator may change it globally on the %s page.' ), 
getAnchor( 
__( 'Network Settings' ), 
network_admin_url( 'settings.php' ) . '#' . WPMYBACKUP_LOGS, 
null, 
true ) );
$help_3 = "'" . $help_3 . "'";
$help_4 = "'" .
_esc( 
'If the backup file size reaches this threshold then it will be splitted<br>into multiple volumes whose size is less than or equal to this number.' ) .
'<br>' . _esc( 'When 0 (zero) then no volume limit thus no spanning.' ) . "'";
$help_5 = "'" . sprintf( 
_esc( 
'If you want to be notified by email after each backup then please enter one or many e-mail addresses separated by comma.<br>The formatting of this string must comply with %s.' ), 
getAnchorE( 'RFC2822', 'http://www.faqs.org/rfcs/rfc2822' ) );
$help_5 .= getExample( 
_esc( 'Example' ), 
'<ul><li>user@example.com, anotheruser@example.com</li><li>' .
htmlentities( '&lt;User&gt; user@example.com, &lt;Another User&gt; anotheruser@example.com' ) .
'</li></ul>', 
false );
$help_5 .= "'";
$help_6 = sprintf( 
_esc( '%s is just an archive (an uncompressed container).' ), 
getAnchorE( 'TAR', 'http://en.wikipedia.org/wiki/Tar_%28computing%29' ) );
$help_6 .= '<br>' . sprintf( 
_esc( '%s, %s, %s are file compression algorithms.' ), 
getAnchorE( 'BZip2', 'http://en.wikipedia.org/wiki/Bzip2' ), 
getAnchorE( 'GZip', 'href=http://en.wikipedia.org/wiki/Gzip' ), 
getAnchorE( 'LZF', 'http://oldhome.schmorp.de/marc/liblzf.html' ) );
$help_6 .= ' ' . sprintf( 
_esc( '%s is both.' ), 
getAnchorE( 'Zip', 'http://en.wikipedia.org/wiki/Zip_%28file_format%29' ) );
$help_6 .= '<br><br>' . sprintf( 
_esc( '<b>Compression ratio</b> : %s (the lower the better)' ), 
getSpanE( 'BZip2', 'green', 'bold' ) . ' < GZip < Zip < LZF' );
$help_6 .= '<br><b>' . sprintf( 
_esc( 'Compression speed</b> : %s (the higher the better)' ), 
getSpanE( 'LZF', 'green', 'bold' ) . ' > GZip > Zip > BZip2' );
$help_6 .= '<br><br>' . readMoreHereE( 
'http://pokecraft.first-world.info/wiki/Quick_Benchmark:_Gzip_vs_Bzip2_vs_LZMA_vs_XZ_vs_LZ4_vs_LZO' );
$help_6 = "'$help_6'";
$help_7 = "'" . _esc( 'This option depends on CygWin.<br>See <b>Expert settings</b>.' ) . "'";
$help_8 = "'" . sprintf( 
_esc( 
'Choose the compression tool you want to use. You can trust always in %s because it is platform independent.<br>On the other hand %s has done a test on your local system and it seems that it is safe to use the local %s tools too.<br>The local %s tool might be better integrated with your %s OS and thus more efficient (eg. uses less memory, compress better/faster).%%s' ), 
WPMYBACKUP, 
WPMYBACKUP, 
PHP_OS, 
PHP_OS, 
PHP_OS ) . "'";
$missing_features = array();
if ( 0 == feature_is_licensed( 'toolchain_ext', $this->license[$this->license_id] ) )
$missing_features[] = 'toolchain_ext';
if ( 0 == feature_is_licensed( 'benchmark', $this->license[$this->license_id] ) )
$missing_features[] = 'benchmark';
$help_8 = sprintf( 
$help_8, 
empty( $missing_features ) ? '' : '<br>' . echoFeatureNotInstalled( 
array( 'toolchain_ext', 'benchmark' ), 
true ) );
$help_9 = "'" .
_esc( 
'This option defines the (verbosity) level of details which are printed-out while running a backup|restore job:<ul>' );
$help_9 .= _esc( 
'<li><b>minimal</b> - prints only a short description (1 line) for the current activity. It is the default (recommended) option.</li>' );
$help_9 .= _esc( '<li><b>compact</b> - minimal + a line for each folder added to the backup archive</li>' );
$help_9 .= _esc( 
'<li><b>full</b> - compact + a line for each file/table added/restored/checked to/from the backup/restore archive. This is recommended when you want debug the backup|restore process or when the process runs on CLI mode.</li>' );
$help_9 .= _esc( 
'</ul><b>Note</b>: if the verbosity level is not minimal and your backup|restore source contains thousands of files|tables then the backup|restore log will print-out thousands of lines which may increase (seriously) the web browser memory footprint and thus your local system responsiveness.' ) .
"'";
require_once $this->getTemplatePath( 'backupjob.php' );
}
protected function getExpertEditorTemplate() {
global $factory_options, $BACKUP_MODE, $registered_ciphres;
$modes_desc = array( 
BACKUP_MODE_FULL => 'All files that are included into the backup regardless when was created their last backup.' );
defined( __NAMESPACE__.'\\BACKUP_MODE_INC' ) &&
$modes_desc[BACKUP_MODE_INC] = 'Only those files will be included which have been changed since the last backup.';
defined( __NAMESPACE__.'\\BACKUP_MODE_DIFF' ) &&
$modes_desc[BACKUP_MODE_DIFF] = 'Only those files will be included which have been changed since the last FULL backup.';
$backup_modes = '';
$help_4 = '';
foreach ( $BACKUP_MODE as $key => $value ) {
$backup_modes .= sprintf( 
'<option value="%s"%s>%s</option>', 
$key, 
$key == $this->settings['mode'] ? ' selected' : '', 
$value );
$help_4 .= sprintf( '<li><b>%s</b> - %s</li>', $value, $modes_desc[$key] );
}
$encryption_opts = '';
$ciphres = array( '' => _esc( 'No encryption' ) );
$separator = function () {
return array( uniqid() => '#' );
};
foreach ( $registered_ciphres as $cipher_class )
$ciphres = $ciphres + $separator() + $cipher_class['items'];
$opt_max_len = array_reduce( 
$ciphres, 
function ( $carry, $item ) {
return max( $carry, strlen( $item ) );
} );
foreach ( $ciphres as $algorithm_name => $algorithm_str )
if ( '#' == $algorithm_str )
$encryption_opts .= '<option disabled>' . str_repeat( '─', $opt_max_len ) . '</option>';
else
$encryption_opts .= sprintf( 
'<option value="%s"%s>%s</option>', 
$algorithm_name, 
$algorithm_name == $this->settings['encryption'] ? ' selected' : '', 
$algorithm_str );
$memory_limit_min = @constant( 'WP_MEMORY_LIMIT' ) ? php_inivalu2int( WP_MEMORY_LIMIT ) / MB : 32; 
$memory_limit_max = getSystemMemoryInfo( 'MemTotal' );
$memory_limit_max = is_numeric( $memory_limit_max ) ? $memory_limit_max / MB : 0;
$max_exec_time_min = 30; 
$max_exec_time_max = 3600; 
$help_1 = "'" .
_esc( 
'On Windows the external compression tool works on top of CygWin.<br>You should specify here the fullpath of <b>bash.exe</b> that comes with CygWin.<br>Additionally, you should specify here any parameter that bash.exe should use by default.<br><br><b>Example</b>: c:\\\\cygwin\\\\bin\\\\bash.exe --login -c' ) .
"'";
$help_2 = "'" . sprintf( 
_esc( 
'Linux and %s respectively can use one of these two versions of BZip:<br><ul style=\\\'list-style-type:square;list-style-position:inside;\\\'><li>BZIP2 that uses one thread only (thus only one CPU)</li><li>%s - which is a parallell implementation of BZIP; it uses all available CPUs</li></ul>' ), 
getAnchorE( 'Cygwin', 'http://en.wikipedia.org/wiki/Cygwin' ), 
getAnchorE( 'PBZIP2', 'http://compression.ca/pbzip2' ) ) . "'";
$help_3 = "'" . _esc( 'The number of miliseconds to sleep the CPU between each compression cycle.' ) . '<br>' . _esc( 
'It throttles the CPU usage so other processes can take advantage of it.' ) . '<br>' .
_esc( 'To disable this feature set this option to zero.' ) . '<br>1 sec = 1000 ms' . "'";
$help_4 = sprintf( 
_esc( 
'Select the way the files are included or not in the backup. The files will be selected and copied into the backup archive depending on what you select here:%s' ), 
'<ul>' . $help_4 . '</ul>' .
_esc( '<b>Note</b> : please note that for the MySQL database is always created a full backup.' ) );
$help_5 = "'" . sprintf( 
_esc( 
'Enter the maximum number of retries on job failure. On job failure<br>the %s will retry to re-run the job this maximum number of<br>times. A job is considered failed when not even a single backup<br>target had not received successfuly the backup file. If the backup<br>has beed successfuly sent to at least one backup destination the job<br>is regarded as (partialy) completed but NOT as a failed job.' ), 
'<b>' . WPMYBACKUP . '</b>' ) . "'";
$help_6 = "'" . _esc( 'Enter the number of <b>seconds</b> to wait between two retries.' ) . "'";
$help_7 = "'" . sprintf( 
_esc( 
'Set the number of seconds the backup job is allowed to run. If this is reached, the script returns a fatal error. The default limit is %s seconds. If set to zero, no time limit is imposed.' ), 
$factory_options['backup']['max_exec_time'][0] ) . "'";
$help_8 = "'" .
_esc( 
'Select one of the encryption algorithms if you want to encrypt the backup archive before it gets uploaded to the target. The encryption is made using a key (K) and a initialization vector (iv) that are automatically generated at install time. You may decrypt manually the file later using any decryption program (although you must provide the algorithm, the key and the vector).' ) .
"'";
$help_9 = "'" .
_esc( 
'The memory limit the PHP script may use. 128MiB should be enough but if you notice that the backup script fails due to `Allowed memory size of XXX bytes exhausted` then try to increase this value.<br>Depending on what plug-ins you have installed and what they are doing the memory used by the backup script may vary. For instance, if you have a profiling plug-in (eg. P3) which monitors what the other plug-ins do then the memory used will be considerably higher (required not by our plug-in functionalities but by the profiling plug-in which traces every call of our plug-in).' ) .
"'";
$help_10 = "'" .
_esc( 
'When this option is set then the file names within the backup archives will include only the website relative path and not the file absolute/real path.' ) . getExample( 
_esc( 'Example' ), 
_esc( 
'If a file fullpath is /some-path/website-root/include/readme.txt then the relative path of the file would be website-root/include/readme.txt' ), 
false ) . "'";
$help_11 = "'" .
_esc( 
'This option automatically creates a safe copy of any individual plug-ins/themes just before Wordpress updates them.' ) .
"'";
require_once $this->getTemplatePath( 'backupjob-expert.php' );
}
}
?>