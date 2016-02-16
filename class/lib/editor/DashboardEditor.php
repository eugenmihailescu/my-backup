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
 * @version : 0.2.3-8 $
 * @commit  : 010da912cb002abdf2f3ab5168bf8438b97133ea $
 * @author  : Eugen Mihailescu eugenmihailescux@gmail.com $
 * @date    : Tue Feb 16 21:44:02 2016 UTC $
 * @file    : DashboardEditor.php $
 * 
 * @id      : DashboardEditor.php | Tue Feb 16 21:44:02 2016 UTC | Eugen Mihailescu eugenmihailescux@gmail.com $
*/

namespace MyBackup;
class DashboardEditor extends AbstractTargetEditor {
private $_stat_manager;
private $_upload_constraint_manual = 'http://php.net/manual/en/ini.core.php';
private $_upload_constraint_link;
private $_restore_upl_chunked;
private function _getJavaScripts() {
global $PROGRESS_PROVIDER;
$this->java_scripts[] = getBackupSourcesJS( $PROGRESS_PROVIDER );
$this->java_scripts[] = "parent.asyncGetJobLog=function(id){parent.asyncGetContent(parent.ajaxurl, 'action=read_folder&log=1&sender=history&nonce=" .
wp_create_nonce_wrapper( 'read_folder' ) . "&id=' + id);};";
ob_start();
?>
parent.update_job_info=function(xmlhttp){
var job_status;
try{
job_status=JSON.parse(xmlhttp.responseText);
}catch(e){
job_status={title:e.message};
}
var array = function(obj) {
var i,result=[];
for(i in obj)
if(obj.hasOwnProperty(i))result.push(obj[i]);
return result;
};
var update=function(sufix,param,index,cindex,is_array){
index=null!==parent.isNull(index,null)?index:-1;
cindex=null!==parent.isNull(cindex,null)?cindex:-1;
is_array=null!==parent.isNull(is_array,null)?is_array:false;
var e=document.getElementById('job_info_'+sufix),data='?',color='';
if(e){
if(job_status.hasOwnProperty(param)){
data=job_status[param];
if(cindex>-1)color=data.hasOwnProperty(cindex)?data[cindex]:'';
if(index>-1)data=data.hasOwnProperty(index)?data[index]:data;
}
if(''!=color)e.style.color=color;
e.innerHTML=is_array?array(data).join(', '):data;
}
};
update('title','title');
update('start','started_time');
update('status','job_status',0,1);
update('state','job_state',0,1);
update('mode','mode');
update('size','jobsize');
update('source','source_type',null,null,true);
update('location','operation',null,null,true);
parent.last_job_id=job_status.hasOwnProperty('id')?job_status.id:0;
parent.last_job=job_status;
if(e=document.getElementById('btn_view_log')){
if(parent.last_job_id)
e.value=e.value.replace(/(\s#\d+)/,'')+' #'+parent.last_job_id;
e.disabled=!parent.last_job_id;
}
if(e=document.getElementById('btn_restore_backup'))
{
if(parent.last_job_id)
e.value=e.value.replace(/(\s#\d+)/,'')+' #'+parent.last_job_id;
e.disabled=!parent.last_job_id;			
}
};
<?php
$this->java_scripts[] = ob_get_clean();
$this->java_scripts[] = 'parent.last_job_id=0;';
$this->java_scripts[] = 'parent.wp_restore_components={};';
$disk_free = disk_free_space( $this->settings['wrkdir'] ? $this->settings['wrkdir'] : sys_get_temp_dir() );
$upload_max_size = min( getUploadLimit(), $disk_free );
$this->_restore_upl_chunked && $upload_max_size = $disk_free;
$this->java_scripts[] = 'parent.upload_max_size=' . $upload_max_size . ';';
$this->java_scripts[] = 'document.getElementById("upload_max_size").innerHTML=parent.getHumanReadableSize(parent.upload_max_size);';
$action = 'last_bak_info';
if ( $this->is_wp ) {
ob_start();
?>
parent.get_last_jobinfo=function(){
parent.asyncGetContent(parent.ajaxurl,'action=%s&nonce=%s&url='+window.location,'__dummy__',parent.update_job_info);
};
parent.get_last_jobinfo();
<?php
$this->java_scripts['Z'] = sprintf( ob_get_clean(), $action, wp_create_nonce_wrapper( $action ) );
$restore_action = 'wp_restore';
$title = _esc( 'WP Restore' );
$on_restore_click = sprintf( 
"jsMyBackup.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,'job_id='+jsMyBackup.last_job_id+'&wp_components='+jsMyBackup.get_selected_wp_components()+'&filter='+jsMyBackup.wp_restore_filter)", 
$restore_action, 
$title, 
wp_create_nonce_wrapper( $restore_action ), 
wp_create_nonce_wrapper( 'get_progress' ), 
wp_create_nonce_wrapper( 'cleanup_progress' ), 
wp_create_nonce_wrapper( 'abort_job' ) );
$this->java_scripts[] = 'parent.run_wp_restore=function(){' . $on_restore_click . ';};';
$on_restore_click1 = sprintf( 
"jsMyBackup.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,'dropin=1',function(){parent.uploader_obj.upload_refresh_files('%s');})", 
$restore_action, 
$title, 
wp_create_nonce_wrapper( $restore_action ), 
wp_create_nonce_wrapper( 'get_progress' ), 
wp_create_nonce_wrapper( 'cleanup_progress' ), 
wp_create_nonce_wrapper( 'abort_job' ), 
wp_create_nonce_wrapper( 'upload_restore_file' ) );
$this->java_scripts[] = 'parent.run_wp_restore1=function(){' . $on_restore_click1 . ';};';
$tmp_dir = isset( $_this_->settings['wrkdir'] ) && ! empty( $_this_->settings['wrkdir'] ) ? $_this_->settings['wrkdir'] : sys_get_temp_dir();
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
$script .= "if (parent.last_job.hasOwnProperty('source_type')) {";
$script .= "var i,j,id;";
$script .= "result += '<ol style=\"list-style-type: none\">';";
$script .= "if(parent.last_job.hasOwnProperty('operation')){";
$script .= "var o='<option value=\"\">" . _esc( 'Any available' ) . "</option>',m;";
$script .= "for(i in parent.last_job.operation)";
$script .= "if(parent.last_job.operation.hasOwnProperty(i)){";
$script .= "m=parent.last_job.operation[i].match(/>([^<]+)/);";
$script .= "if(null!=m)m=m[1];else m=parent.backup_sources[i];";
$script .= "o+='<option value=\"'+i+'\">'+m+'</option>';";
$script .= "}";
$script .= "result+='<li>';";
$script .= "result+='<label for=\"wp_restore_source_filter\">" . _esc( 'Filter by source' ) . " : </label>';";
$script .= "result += '<select id=\"wp_restore_source_filter\" onchange=\"jsMyBackup.wp_restore_filter_items(this);\">'+o+'</select>';";
$script .= "result+='</li>';";
$script .= "}";
$script .= "for (i in parent.last_job.source_type)";
$script .= "if (parent.last_job.source_type.hasOwnProperty(i)) {";
$script .= "id = 'source_type_' + i.replace('-', '$');";
$script .= "result += '<li><input type=\"checkbox\" id=\"' + id + '\" onchange=\"var el=this.parentNode.getElementsByTagName(&quot;input&quot;);for(var i=0;i<el.length;i+=1)if(el[i].id!=this.id){el[i].checked=this.checked;el[i].onchange();}\"><label for=\"' + id + '\">' + parent.last_job.source_type[i].replace(/.*>([^<]*)<.*/, '$1') + '</label>';";
$script .= "if (parent.last_job.hasOwnProperty('files') && parent.last_job.files.hasOwnProperty(i)) {";
$script .= "result += '<ol style=\"list-style-type: none\">';";
$script .= "for (j in parent.last_job.files[i])";
$script .= "if (parent.last_job.files[i].hasOwnProperty(j))";
$script .= "result += '<li class=\"wp_restore_input wp_restore_input'+parent.last_job.files[i][j][2]+'\"><input type=\"checkbox\" id=\"'+j+'\" onchange=\"jsMyBackup.set_wp_restore_components(id,this.checked);\"><label '+(" .
DISK_TARGET .
"==parent.last_job.files[i][j][2]?'class=\"highlight-label\"':'')+' for=\"'+j+'\">' + parent.last_job.files[i][j][0].replace(/.*[\\\/](.*)/, '$1') + '</label><span>('+parent.last_job.files[i][j][1]+')</span></li>';";
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
$this->_upload_constraint_link = array( 
getAnchor( 'upload_max_filesize', $this->_upload_constraint_manual . '#ini.upload-max-filesize' ), 
getAnchor( 'post_max_size', $this->_upload_constraint_manual . '#ini.post-max-size' ) );
$this->_restore_upl_chunked = strToBool( $this->settings['restore_upl_chunked'] );
$this->_getJavaScripts();
$this->_stat_manager = getJobsStatManager( $this->settings );
}
protected function getEditorTemplate() {
global $COMPRESSION_NAMES;
$backup_action = 'run_backup';
$on_backup_click = sprintf( 
"jsMyBackup.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,null,jsMyBackup.get_last_jobinfo);", 
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
"jsMyBackup.wp_restore_components={};jsMyBackup.wp_restore_filter='';jsMyBackup.popupConfirm('%s','%s'+jsMyBackup.get_wp_restore_components_html(),null,{'%s':'jsMyBackup.removePopupLast();jsMyBackup.run_wp_restore();','%s':null},null);", 
_esc( 'WP Restore' ), 
htmlspecialchars( $html ), 
$restore_btn, 
$cancel_btn );
$on_restore_click1 = 'jsMyBackup.run_wp_restore1();';
} elseif ( defined( __NAMESPACE__.'\\JOB_RESTORE' ) ) {
$on_restore_click = '';
$on_restore_click1 = '';
}
$on_viewlog_click = 'jsMyBackup.asyncGetJobLog(jsMyBackup.last_job_id);';
$next_schedule = $this->is_wp ? wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME ) : 'TBD';
$next_schedule = empty( $next_schedule ) ? _esc( 'undefined' ) : date( DATETIME_FORMAT, $next_schedule );
require_once $this->getTemplatePath( 'dashboard.php' );
}
protected function getExpertEditorTemplate() {
$is_dashboard = true;
require_once $this->getTemplatePath( 'restore-expert.php' );
}
}
?>