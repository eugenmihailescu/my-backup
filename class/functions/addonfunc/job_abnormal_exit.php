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
 * @version : 0.2.3-27 $
 * @commit  : 10d36477364718fdc9b9947e937be6078051e450 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 10:06:27 2016 +0100 $
 * @file    : job_abnormal_exit.php $
 * 
 * @id      : job_abnormal_exit.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

global $TARGET_NAMES;
$job_id = $_this_->method['id'];
include_once ADDONS_PATH . '99-cleanup.php';
$message = sprintf( 
_esc( 'The job #%s has been terminated abnormally (?!). Check the %s.' ), 
$job_id, 
getAnchor( _esc( 'error log' ), getTabLink( $TARGET_NAMES[APP_LOGS], true ), '_self' ) );
add_alert_message( $message, $job_id );
cleanTransitoryBackupFiles( $_this_->settings, $job_id );
unlockLongRunningJob( $_this_->settings, true, $job_id );
echo $message;
?>