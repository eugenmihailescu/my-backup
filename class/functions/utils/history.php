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
 * @version : 0.2.0-10 $
 * @commit  : bc79573e2975a220cb1cfbb08b16615f721a68c5 $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Mon Sep 14 21:14:57 2015 +0200 $
 * @file    : history.php $
 * 
 * @id      : history.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

function getJobsStatManager($opts = null) {
global $settings;
if (null == $opts)
$opts = $settings;
if ($opts ['historydb'] == 'sqlite')
$params = STATISTICS_LOGFILE;
elseif ($opts ['mysql_enabled'])
$params = array (
'host' => $opts ['mysql_host'],
'port' => $opts ['mysql_port'],
'db_name' => $opts ['mysql_db'],
'user' => $opts ['mysql_user'],
'pwd' => $opts ['mysql_pwd'] 
);
else
throw new MyException ( _esc ( 'MySQL feature not enabled. You should either enable MySQL or use the SQLite instead.' ) );
return new StatisticsManager ( $params, $opts );
}
?>
