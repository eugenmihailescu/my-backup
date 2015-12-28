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
 * @file    : DashboardEditor.php $
 * 
 * @id      : DashboardEditor.php | Mon Dec 28 17:57:55 2015 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class DashboardEditor extends AbstractTargetEditor {
private $_stat_manager;
private function _getJavaScripts() {
global $PROGRESS_PROVIDER;
$this->java_scripts[] = getBackupSourcesJS( $PROGRESS_PROVIDER );
$this->java_scripts[] = "parent.asyncGetJobLog=function(id){parent.asyncGetContent(parent.ajaxurl, 'action=read_folder&log=1&sender=history&nonce=" .
wp_create_nonce_wrapper( 'read_folder' ) . "&id=' + id);};";
ob_start();
?>
parent.update_job_info=function(xmlhttp){
var job_status=JSON.parse(xmlhttp.responseText),e;
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
parent.last_job_id=job_status.hasOwnProperty('id')?job_status['id']:0;
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
$action = 'last_bak_info';
ob_start();
?>
parent.get_last_jobinfo=function(){
parent.asyncGetContent(parent.ajaxurl,'action=%s&nonce=%s&url='+window.location,'__dummy__',parent.update_job_info);
};
<?php
$this->java_scripts[] = sprintf( ob_get_clean(), $action, wp_create_nonce_wrapper( $action ) );
$this->java_scripts[] = 'parent.get_last_jobinfo();';
if ( $this->is_wp ) {
$restore_action = 'wp_restore';
$title = _esc( 'WP Restore' );
$on_restore_click = sprintf( 
"js56816a36b58dc.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,'job_id='+js56816a36b58dc.last_job_id+'&wp_components='+js56816a36b58dc.get_selected_wp_components());", 
$restore_action, 
$title, 
wp_create_nonce_wrapper( $restore_action ), 
wp_create_nonce_wrapper( 'get_progress' ), 
wp_create_nonce_wrapper( 'cleanup_progress' ), 
wp_create_nonce_wrapper( 'abort_job' ) );
$this->java_scripts[] = 'parent.run_wp_restore=function(){' . $on_restore_click . ';};';
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
$script = "parent.get_wp_restore_components_html = function() {";
$script .= "var result = '';";
$script .= "if (parent.last_job.hasOwnProperty('source_type')) {";
$script .= "var i, id;";
$script .= "result += '<ol style=\"list-style-type: none\">';";
$script .= "for (i in parent.last_job.source_type)";
$script .= "if (parent.last_job.source_type.hasOwnProperty(i)) {";
$script .= "id = 'source_type_' + i.replace('-', '$');";
$script .= "result += '<li><input type=\"checkbox\" id=\"' + id + '\" onchange=\"var el=this.parentNode.getElementsByTagName(&quot;input&quot;);for(var i=0;i<el.length;i+=1)if(el[i].id!=this.id){el[i].checked=this.checked;el[i].onchange();}\"><label for=\"' + id + '\">' + parent.last_job.source_type[i].replace(/.*>([^<]*)<.*/, '$1') + '</label>';";
$script .= "if (parent.last_job.hasOwnProperty('files') && parent.last_job.files.hasOwnProperty(i)) {";
$script .= "result += '<ol style=\"list-style-type: none\">';";
$script .= "for (j in parent.last_job.files[i])";
$script .= "if (parent.last_job.files[i].hasOwnProperty(j))";
$script .= "result += '<li><input type=\"checkbox\" id=\"'+j+'\" onchange=\"js56816a36b58dc.set_wp_restore_components(id,this.checked);\"><label for=\"'+j+'\">' + parent.last_job.files[i][j][0].replace(/.*[\\\/](.*)/, '$1') + '</label><span>('+parent.last_job.files[i][j][1]+')</span></li>';";
$script .= "result += '</ol>'";
$script .= "}";
$script .= "result += '</li>';";
$script .= "}";
$script .= "result += '</ol>';";
$script .= "}";
$script .= "return result;";
$script .= "};";
$this->java_scripts[] = $script;
}
}
protected function initTarget() {
parent::initTarget();
$this->_getJavaScripts();
$this->_stat_manager = getJobsStatManager( $this->settings );
}
protected function getEditorTemplate() {
$backup_action = 'run_backup';
$on_backup_click = sprintf( 
"js56816a36b58dc.asyncRunBackup('%s','%s','%s','%s','%s','%s',null,null,js56816a36b58dc.get_last_jobinfo);", 
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
"js56816a36b58dc.wp_restore_components={};js56816a36b58dc.popupConfirm('%s','%s'+js56816a36b58dc.get_wp_restore_components_html(),null,{'%s':'js56816a36b58dc.removePopupLast();js56816a36b58dc.run_wp_restore();','%s':null},null);", 
_esc( 'WP Restore' ), 
htmlspecialchars( $html ), 
$restore_btn, 
$cancel_btn );
} elseif ( defined( __NAMESPACE__.'\\JOB_RESTORE' ) ) {
$on_restore_click = sprintf( '' );
}
$on_viewlog_click = 'js56816a36b58dc.asyncGetJobLog(js56816a36b58dc.last_job_id);';
$next_schedule = $this->is_wp ? wp_next_scheduled( WPCRON_SCHEDULE_HOOK_NAME ) : 'TBD';
$next_schedule = empty( $next_schedule ) ? _esc( 'undefined' ) : date( DATETIME_FORMAT, $next_schedule );
require_once $this->getTemplatePath( 'dashboard.php' );
}
protected function getExpertEditorTemplate() {
require_once $this->getTemplatePath( 'restore-expert.php' );
}
}
?>