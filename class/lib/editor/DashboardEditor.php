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
 * @file    : DashboardEditor.php $
 * 
 * @id      : DashboardEditor.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class DashboardEditor extends AbstractTargetEditor {
private $_last_job;
private $_enabled_targets;
private $_stat_manager;
private $_upload_constraint_manual;
private $_upload_constraint_link;
private $_restore_upl_chunked;
private function _getJavaScripts() {
global $PROGRESS_PROVIDER;
$this->java_scripts[] = 'parent.enabled_backup_targets=[' . implode( ',', $this->_enabled_targets ) . '];';
$action_1 = 'wp_jobs_stats';
if ( ! _dir_in_allowed_path( $this->settings['wrkdir'] ) )
$disk_free = PHP_INT_MAX;
else
$disk_free = _disk_free_space( $this->settings['wrkdir'] ? $this->settings['wrkdir'] : _sys_get_temp_dir() );
$upload_max_size = min( getUploadLimit(), $disk_free );
$this->_restore_upl_chunked && $upload_max_size = $disk_free;
$this->java_scripts[] = getBackupSourcesJS( $PROGRESS_PROVIDER );
$this->java_scripts[] = "parent.asyncGetJobLog=function(id){parent.asyncGetContent(parent.ajaxurl, 'action=read_folder&log=1&sender=history&nonce=" .
wp_create_nonce_wrapper( 'read_folder' ) . "&id=' + id);};";
ob_start();
?>
parent.stat_info={};
parent.update_stat_element=function(sufix,param,index,fgindex,bgindex,is_array){
parent.update_element(sufix,param,index,fgindex,bgindex,is_array,'stat_info');
};
parent.update_wp_jobs_stats=function(xmlhttp){
try{
parent.stat_info=JSON.parse(xmlhttp.responseText);
if(!parent.isNull(xmlhttp.has_cookie,false)){
var expire=<?php echo time();?>-parent.stat_info.timestamp;
parent.setCookie('<?php echo $action_1;?>',xmlhttp.responseText,expire>1?expire+'s':'3600s');
}
}catch(e){
parent.stat_info={title:e.message};
console.log(xmlhttp);
}
parent.update_stat_element('title','title');
parent.update_stat_element('bak_done','backup_count');
parent.update_stat_element('rst_done','restoration_count');
parent.update_stat_element('files_count','files_count');
parent.update_stat_element('ratio','ratio');
parent.update_stat_element('files_size','file_size');
parent.update_stat_element('data_size','data_size');
};
<?php
$this->java_scripts[] = ob_get_clean();
$this->java_scripts[] = 'parent.last_bak_job_id=0;parent.last_rst_job_id=0';
$this->java_scripts[] = 'parent.wp_restore_components={};';
$this->java_scripts[] = 'parent.upload_max_size=' . $upload_max_size . ';';
$this->java_scripts[] = 'document.getElementById("upload_max_size").innerHTML=parent.getHumanReadableSize(parent.upload_max_size);';
ob_start();
?>
parent.onJobDone = function(xhr, job_id) {
parent.onJobAbnormalExit(xhr, job_id,'<?php echo wp_create_nonce_wrapper( 'job_abnormal_exit' );?>','<?php echo wp_create_nonce_wrapper( 'wp_jobs_stats' );?>');
parent.get_last_jobinfo(true);
};
<?php
$this->java_scripts[] = ob_get_clean();
if ( $this->is_wp ) {
ob_start();
echo $this->_last_job['js'];
?>
parent.get_wp_jobs_stats=function(nocache){
nocache=parent.isNull(nocache,false);
if(!nocache)
{
var cookie=parent.getCookie('<?php echo $action_1;?>');
if(cookie){
return parent.update_wp_jobs_stats({responseText:cookie,has_cookie:true});
}
}
parent.asyncGetContent(parent.ajaxurl,'action=<?php echo $action_1;?>&nonce=<?php echo  wp_create_nonce_wrapper( $action_1 );?>&'+(nocache?'nocache=1&':'')+'url='+encodeURIComponent(window.location),parent.dummy,parent.update_wp_jobs_stats);
};
parent.get_wp_jobs_stats();
<?php
$this->java_scripts['Z'] = ob_get_clean();
$restore_action = 'wp_restore';
$title = _esc( 'WP Restore' );
$on_restore_click = sprintf( 
"parent.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,'job_id='+jsMyBackup.last_bak_job_id+'&wp_components='+parent.get_selected_wp_components()+'&filter='+parent.wp_restore_filter,function(xhr){parent.get_wp_jobs_stats(true);parent.onJobDone(xhr,'\\\\d+');})", 
$restore_action, 
$title, 
wp_create_nonce_wrapper( $restore_action ), 
wp_create_nonce_wrapper( 'get_progress' ), 
wp_create_nonce_wrapper( 'cleanup_progress' ), 
wp_create_nonce_wrapper( 'abort_job' ) );
$this->java_scripts[] = 'parent.run_wp_restore=function(){' . $on_restore_click . ';};';
$on_restore_click1 = sprintf( 
"jsMyBackup.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,'dropin=1',function(xhr){parent.uploader_obj.upload_refresh_files('%s');parent.get_wp_jobs_stats(true);parent.onJobDone(xhr,'\\\\d+');})", 
$restore_action, 
$title, 
wp_create_nonce_wrapper( $restore_action ), 
wp_create_nonce_wrapper( 'get_progress' ), 
wp_create_nonce_wrapper( 'cleanup_progress' ), 
wp_create_nonce_wrapper( 'abort_job' ), 
wp_create_nonce_wrapper( 'upload_restore_file' ) );
$this->java_scripts[] = 'parent.run_wp_restore1=function(){' . $on_restore_click1 . ';};';
$tmp_dir = isset( $this->settings['wrkdir'] ) && ! empty( $this->settings['wrkdir'] ) ? $this->settings['wrkdir'] : dirname(LOG_DIR);
$tmp_dir = addTrailingSlash( $tmp_dir );
$dropin_dir = $tmp_dir . addTrailingSlash( DROPIN_RESTORE );
ob_start();
?>
parent.set_wp_restore_components=function(id,flag){
parent.wp_restore_components[id]=flag;
};
<?php
$this->java_scripts[] = ob_get_clean();
ob_start();
?>
parent.get_selected_wp_components=function(){
var result=[],i;
for(i in parent.wp_restore_components)
if(parent.wp_restore_components.hasOwnProperty(i)&&true===parent.wp_restore_components[i])
result.push(i);
return result.join("|");
};
<?php
$this->java_scripts[] = ob_get_clean();
ob_start();
?>
parent.wp_restore_filter_items=function(sender){
var has_class=function(element,cls){
return element.className.split(' ').indexOf(cls) > -1;
};
var filter=sender.options[sender.selectedIndex].value, inputs=document.querySelectorAll('.wp_restore_input'),i,s,c;
parent.wp_restore_filter=filter;
for(i=0;inputs.length>i;i+=1){
s=has_class(inputs[i],'wp_restore_input'+filter);
inputs[i].style.display=s?'inherit':'none';
c=inputs[i].getElementsByTagName('input');
if(c.length){
c[0].checked=s;
c[0].onchange();
}
}
};		
<?php
$this->java_scripts[] = ob_get_clean();
$script = "parent.get_wp_restore_components_html = function() {";
$script .= "var result = '';";
$script .= "if (parent.last_job[" . JOB_BACKUP . "].hasOwnProperty('source_type')) {";
$script .= "var i,j,id,job_issue=false;";
$script .= sprintf( 
'if(2!=parent.last_job[%d].job_status[2])job_issue="%s";else if(2==parent.last_job[%d].job_state[2])job_issue="%s";', 
JOB_BACKUP, 
_esc( 'not finished successfully' ), 
JOB_BACKUP, 
_esc( 'failed' ) );
$script .= sprintf( 
"if(false!=job_issue)result+=\"<div class='redcaption' style='text-align:center;padding:5px;'>%s</div>\";", 
sprintf( _esc( "This job was %s and therefore its restoration is not recommended." ), '"+job_issue+"' ) );
$script .= "result += '<ol style=\"list-style-type: none\">';";
$script .= "if(parent.last_job[" . JOB_BACKUP . "].hasOwnProperty('operation')){";
$script .= "var o='<option value=\"\">" . _esc( 'Any available' ) . "</option>',m;";
$script .= "for(i in parent.last_job[" . JOB_BACKUP . "].operation)";
$script .= "if(parent.last_job[" . JOB_BACKUP . "].operation.hasOwnProperty(i)){";
$script .= "m=parent.last_job[" . JOB_BACKUP . "].operation[i].match(/>([^<]+)/);";
$script .= "if(null!=m)m=m[1];else m=parent.backup_sources[i];";
$script .= "o+='<option value=\"'+i+'\">'+m+'</option>';";
$script .= "}";
$script .= "result+='<li>';";
$script .= "result+='<label for=\"wp_restore_source_filter\">" . _esc( 'Filter by source' ) . " : </label>';";
$script .= "result += '<select id=\"wp_restore_source_filter\" onchange=\"jsMyBackup.wp_restore_filter_items(this);\">'+o+'</select>';";
$script .= "result+='</li>';";
$script .= "}";
$script .= "for (i in parent.last_job[" . JOB_BACKUP . "].source_type)";
$script .= "if (parent.last_job[" . JOB_BACKUP . "].source_type.hasOwnProperty(i)) {";
$script .= "id = 'source_type_' + i.replace('-', '$');";
$script .= "result += '<li><input type=\"checkbox\" id=\"' + id + '\" onchange=\"var el=this.parentNode.getElementsByTagName(&quot;input&quot;);for(var i=0;i<el.length;i+=1)if(el[i].id!=this.id){el[i].checked=this.checked;el[i].onchange();}\"><label for=\"' + id + '\">' + parent.last_job[" .
JOB_BACKUP . "].source_type[i].replace(/.*>([^<]*)<.*/, '$1') + '</label>';";
$script .= "if (parent.last_job[" . JOB_BACKUP . "].hasOwnProperty('files') && parent.last_job[" . JOB_BACKUP .
"].files.hasOwnProperty(i)) {";
$script .= "result += '<ol style=\"list-style-type: none\">';";
$script .= "for (j in parent.last_job[" . JOB_BACKUP . "].files[i])";
$script .= "if (parent.last_job[" . JOB_BACKUP . "].files[i].hasOwnProperty(j))";
$script .= "result += '<li class=\"wp_restore_input wp_restore_input'+parent.last_job[" . JOB_BACKUP .
"].files[i][j][2]+'\"><input type=\"checkbox\" id=\"'+j+'\" onchange=\"jsMyBackup.set_wp_restore_components(id,this.checked);\" data-type=\"'+i+'\"><label '+(" .
DISK_TARGET . "==parent.last_job[" . JOB_BACKUP .
"].files[i][j][2]?'class=\"highlight-label\"':'')+' for=\"'+j+'\">' + parent.last_job[" . JOB_BACKUP .
"].files[i][j][0].replace(/.*[\\\/](.*)/, '$1') + '</label><span>('+parent.last_job[" . JOB_BACKUP .
"].files[i][j][1]+')</span></li>';";
$script .= "result += '</ol>';";
$script .= "}";
$script .= "result += '</li>';";
$script .= "}";
$script .= "result += '</ol>';";
$script .= "}";
$script .= "return result;";
$script .= "};";
$this->java_scripts[] = $script;
$upload_action = 'upload_restore_file';
$upload_nonce = wp_create_nonce_wrapper( $upload_action );
ob_start();
?>
var dashboard_uploader=new MyBackupDashboardJS(parent, MyChunkUploader);
dashboard_uploader.options={ 
chunked_upload : <?php echo $this->_restore_upl_chunked?'true':'false';?>,
max_chunk_size : <?php echo 1024*$this->settings['upload_max_chunk_size'];?>,
raw_post : false,
max_parallel_chunks : <?php echo $this->settings['upload_max_parallel_chunks'];?>,
send_interval : <?php echo $this->settings['upload_send_interval'];?>,
wait_timeout : <?php echo $this->settings['max_exec_time'];?>,
dropin_path : '<?php echo normalize_path($dropin_dir);?>',
upload_action : '<?php echo $upload_action;?>',
upload_nonce : '<?php echo $upload_nonce;?>',
container_class : '<?php echo $this->container_shape;?>' };
dashboard_uploader.strings = { 
abort_confirm : '<?php _pesc('Are you sure?');?>',
abort_msg : '<?php _pesc('Aborted by user');?>',
restore_btn : '<?php _pesc('Restore %1 file(s)');?>',
remove_btn : '<?php _pesc('Remove');?>',
abort_btn : '<?php _pesc('Abort');?>',
unknown_str : '<?php _pesc('unknown');?>',
error_str : '<?php _pesc('Error');?>',
warning_str : '<?php _pesc('Warning');?>',
file_not_supported : '<?php _pesc('Either the File, FileReader, FileList or Blob types are not supported by your browser.');?>',
file_hint : '<?php printf(_esc('You may however restore a file by uploading it %s to %s'),getAnchor(_esc('manually'), lmgtfy('How do I upload a file to my server')),getSpan(normalize_path($dropin_dir),null,'bold'));?>',
formdata_not_supported : '<?php _pesc('Your browser does not support FormData, therefore the file upload function is disabled.');?>',
dragevent_not_supported : '<?php _pesc('Your browser does not support the DragEvent interface');?>',
dragevent_hint1 : '<?php _pesc('You may use the button below to select the file(s).');?>',
dragevent_hint2 : '<?php _pesc('either, therefore the drag & drop function is disabled.');?>',
size_exceeded_msg : '<?php _pesc('The size of %1 (%2) exceeds the %3 (%4).');?>',
size_exceeded_hint : '<?php printf(_esc('Check out the `%s` expert option.'),_esc('Upload files in chunks'));?>' ,
file_select_bug:'<?php _pesc('The number of selected files (%1) differs the number of files seen by your browser (%2). Please add one file at a time or use a newer browser.');?>'
};
dashboard_uploader.init();
parent.uploader_obj = dashboard_uploader.get_uploader();
<?php
$this->java_scripts[] = ob_get_clean();
}
}
protected function initTarget() {
parent::initTarget();
global $BACKUP_TARGETS;
$this->_enabled_targets = array();
foreach ( $BACKUP_TARGETS as $target => $opt )
strToBool( getParam( $this->settings, $opt . '_enabled', 0 ) ) && $this->_enabled_targets[] = $target;
$this->_upload_constraint_manual = PHP_MANUAL_URL . 'ini.core.php';
$this->_upload_constraint_link = array( 
getAnchor( 'upload_max_filesize', $this->_upload_constraint_manual . '#ini.upload-max-filesize' ), 
getAnchor( 'post_max_size', $this->_upload_constraint_manual . '#ini.post-max-size' ) );
$this->_restore_upl_chunked = strToBool( $this->settings['restore_upl_chunked'] );
$container_shape = $this->container_shape;
include_once $this->getTemplatePath( 'dashboard-job-info.php' );
$this->_last_job = array( 'html' => $last_job_html, 'js' => $last_job_js );
$this->_getJavaScripts();
$this->_stat_manager = getJobsStatManager( $this->settings );
}
protected function getEditorTemplate() {
global $COMPRESSION_NAMES;
$backup_action = 'run_backup';
$on_backup_click = sprintf( 
"if(!jsMyBackup.enabled_backup_targets.length)return jsMyBackup.popupError('%s','%s');jsMyBackup.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,null,jsMyBackup.onJobDone);", 
_esc( 'Error' ), 
sprintf( _esc( 'You have not selected any %s option.' ), getTabAnchorE( APP_TABBED_TARGETS ) ), 
$backup_action, 
_esc( 'Backup' ), 
wp_create_nonce_wrapper( $backup_action ), 
wp_create_nonce_wrapper( 'get_progress' ), 
wp_create_nonce_wrapper( 'cleanup_progress' ), 
wp_create_nonce_wrapper( 'abort_job' ) );
$restore_disable = ! ( $this->is_wp || defined( __NAMESPACE__.'\\JOB_RESTORE' ) ) ? 'disabled' : '';
if ( $this->is_wp ) {
$restore_btn = _esc( 'Restore Now' );
$cancel_btn = _esc( 'Cancel' );
$html = sprintf( '<div class="hintbox %s">', $this->container_shape ) .
_esc( 
'This screen will help you to restore the WP components shown below from the choosen backup.<br>This means that the current WP files/data will be replaced by those selected below.' );
$html .= '<br>' . sprintf( 
_esc( 'Press %s to start restoring the chosen WP components or %s to abort this command.' ), 
'<strong>' . $restore_btn . '</strong>', 
'<strong>' . $cancel_btn . '</strong>' );
$html .= '</div>';
$on_restore_click = sprintf( 
"jsMyBackup.wp_restore_components={};jsMyBackup.wp_restore_filter='';jsMyBackup.popupConfirm('%s','%s'+jsMyBackup.get_wp_restore_components_html()+jsMyBackup.restore_alert,null,{'%s':'jsMyBackup.removePopupLast();jsMyBackup.run_wp_restore();','%s':null},%d);", 
_esc( 'WP Restore' ), 
htmlspecialchars( $html ), 
$restore_btn, 
$cancel_btn, 
DEFAULT_JSPOPUP_WIDTH + 100 );
$on_restore_click1 = 'jsMyBackup.run_wp_restore1();';
} elseif ( defined( __NAMESPACE__.'\\JOB_RESTORE' ) ) {
$on_restore_click = '';
$on_restore_click1 = '';
}
$on_viewlog_click = 'jsMyBackup.asyncGetJobLog(jsMyBackup.last_bak_job_id);';
$container_shape = $this->container_shape;
require_once $this->getTemplatePath( 'dashboard.php' );
}
protected function getExpertEditorTemplate() {
$is_dashboard = true;
require_once $this->getTemplatePath( 'restore-expert.php' );
}
}
?>