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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : post-config.php $
 * 
 * @id      : post-config.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

$PROGRESS_PROVIDER = $BACKUP_TARGETS + array_intersect_key ( $TARGET_NAMES, array_flip ( $NOT_BACKUP_TARGETS ) );
$BACKUP_TARGETS = array_diff_key ( $PROGRESS_PROVIDER, array_flip ( $NOT_BACKUP_TARGETS ) );
! isset ( $REGISTERED_BACKUP_TABS ) && $REGISTERED_BACKUP_TABS = array (); 
$REGISTERED_BACKUP_TABS = $REGISTERED_BACKUP_TABS + $BACKUP_TARGETS;
arrayKeySort ( $REGISTERED_BACKUP_TABS, array (
DISK_TARGET,
FTP_TARGET,
SSH_TARGET,
DROPBOX_TARGET,
WEBDAV_TARGET 
) );
?>
