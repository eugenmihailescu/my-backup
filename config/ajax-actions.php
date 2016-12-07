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
 * @version : 0.2.3-37 $
 * @commit  : 56326dc3eb5ad16989c976ec36817cab63bc12e7 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Dec 7 18:54:23 2016 +0100 $
 * @file    : ajax-actions.php $
 * 
 * @id      : ajax-actions.php | Wed Dec 7 18:54:23 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function get_valid_ajax_actions(){
return array('abort_job','addon_disable','addon_install','addon_uninstall','after_nonce','anonymousExec','auto_save','braintree_proxy','check_update','check_vat','checkout','chkRegistered','chk_lic','chk_status','cleanup_progress','clear_log','compression_benchmark','decrypt_file','del_dir','del_file','del_lic','del_oauth','del_target','del_wpcron_schedule','delhist','dwl_file','dwl_sql_script','edit_step','enable_target','encryption_info','eula','export_settings','feat_lic','feat_table','flushhist','formatError','ftp_exec','gen_encrypt_keys','getLicenseInfo','getSupportFormInfo','get_chart','get_progress','get_wpcron_schedule','import_settings','initStorage','install_update','isValidSupportForm','job_abnormal_exit','last_job_info','log_read','log_read_abort','mk_dir','mybackup_core_backup','mybackup_dismiss_dashboard_notice','php_setup','print_debug_sample','processFolderRequest','processSupportForm','read_alert','read_folder','read_folder_info','redir_checkout','ren_file','reset_defaults','resthist','restore_mysql','rst_file','run_backup','run_mysql_maint','run_parallel_backup','run_restore','save_target_desc','search_rest_file','send_email','set_wpcron_schedule','srcfiles_recalc_cache','submit_options','submit_order','support_sender_info','support_sender_send','support_sender_validate','test_dwl','test_dwl','update_info','upload_restore_file','validateSupportForm','wp_jobs_stats','wp_restore');}
?>