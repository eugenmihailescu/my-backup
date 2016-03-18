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
 * @file    : processFolderRequest.php $
 * 
 * @id      : processFolderRequest.php | Fri Mar 18 10:06:27 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

$action = isNull ( $_this_->method, 'action' );
$service = null == $action || 'read_folder' != $action ? $_this_->method ['service'] : $_this_->method ['sender'];
$_this_->session = isNull ( $_this_->method, 'root' );
$_this_->storage = null;
$_this_->initStorage ( $service );
switch ($action) {
case 'read_folder' :
switch ($service) {
case 'wpsource' :
case 'fssource' :
require_once EDITOR_PATH . 'target-content-functions.php';
echoFileListContent ( $_this_->method, $_this_->settings );
break;
case 'history' :
require_once VIEWER_PATH . 'job-history-functions.php';
if (isset ( $_this_->method ['log'] ))
echoHistoryJobLog ( $_this_->method, $_this_->settings );
else
echoHistoryContent ( $_this_->method, $_this_->settings, true );
break;
case 'support' :
require_once VIEWER_PATH . 'support-functions.php';
echoTicketList ( $_this_->method, $_this_->anonymousExec ( 'getLicenseInfo' ), $_this_->settings );
break;
case 'email' :
require_once VIEWER_PATH . 'support-functions.php';
echoTicketList ( $_this_->method, $_this_->anonymousExec ( 'getLicenseInfo' ), $_this_->settings );
break;
case 'notification' :
require_once VIEWER_PATH . 'notification-functions.php';
if (isset ( $_this_->method ['detail'] ))
echoMessageDetail ( $_this_->method, $_this_->settings );
elseif (isset ( $_this_->method ['flush'] ))
flushMessageList ( $_this_->method, $_this_->settings );
else
echoMessageList ( $_this_->method, $_this_->settings );
break;
default :
echoFolder ( $service, isNull ( $_this_->method, 'path', $_this_->settings [$service] ), isNull ( $_this_->method, 'path_id', $_this_->settings [$service . '_path_id'] ), $_this_->method ['filter'], $_this_->method ['files_function'], $_this_->method ['directory_separator'], isNull ( $_this_->method, 'download_redirect' ), $_this_->method ['folder_style'], $_this_->method ['folder_content'], $_this_->storage, $_this_->settings );
break;
}
break;
case 'read_folder_info' :
echoFolderInfo ( $service, $_this_->session, $_this_->storage, $_this_->method, $_this_->settings );
break;
}
?>