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
 * @version : 0.2.3-34 $
 * @commit  : 433010d91adb8b1c49bace58fae6cd2ba4679447 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Nov 30 15:38:35 2016 +0100 $
 * @file    : 99-cleanup.php $
 * 
 * @id      : 99-cleanup.php | Wed Nov 30 15:38:35 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function unlockLongRunningJob($settings, $forced = false, $job_id = null)
{
if (! _is_file(JOBS_LOCK_FILE))
return array();
include_once LIB_PATH . 'LogFile.php';
if (! $last_job_timestamp = @filemtime(JOBS_LOCK_FILE)) {
$jobs_log = new LogFile(JOBS_LOGFILE, $settings);
$last_job_id = $jobs_log->getLastJobId($job_id);
$last_job_timestamp = false !== $last_job_id ? strtotime($last_job_id[1]) : false;
}
$diff = time() - $last_job_timestamp;
if ($forced || ($diff > SECDAY)) {
if ($flock = fopen(JOBS_LOCK_FILE, 'r')) {
@flock($flock, LOCK_UN);
fclose($flock);
@unlink(JOBS_LOCK_FILE) && add_alert_message(sprintf(_esc('Unlocked the job lock file (created %s sec ago) forcebly'), getHumanReadableTime($diff)), $job_id);
}
}
return array();
}
function cleanTransitoryBackupFiles($settings, $job_id = null)
{
$wrkdir = addTrailingSlash(getParam($settings, 'wrkdir', _sys_get_temp_dir()));
$files = glob($wrkdir . WPMYBACKUP_LOGS . '_*');
$arc_name = getParam($settings, 'name');
$url = getParam($settings, 'url', 'backup');
if (null == $arc_name || TRUE === $arc_name) {
$arc_name = sprintf("%s-%s-%s", $url, str_repeat('?', 8), str_repeat('?', 6));
}
$arcs = glob($wrkdir . $arc_name . '*');
$files || $files = $arcs;
$files && $arcs && $files = array_merge($files, $arcs);
if (! empty($files)) {
foreach ($files as $filename)
@unlink($filename);
add_alert_message(sprintf(_esc('Found and deleted %d redidual files (%s*) from %s'), count($files), $arc_name, $wrkdir), $job_id);
}
return array();
}
if (! _is_file(JOBS_LOCK_FILE)) {
is_session_started();
$session_key = 'mynix_cleanup_check';
if (! isset($_SESSION[$session_key]) || (time() - $_SESSION[$session_key] > SECDAY)) {
add_session_var($session_key, time());
register_settings('cleanTransitoryBackupFiles');
}
} else {
register_settings('unlockLongRunningJob');
}
?>