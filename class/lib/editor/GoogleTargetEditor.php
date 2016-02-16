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
 * @file    : GoogleTargetEditor.php $
 * 
 * @id      : GoogleTargetEditor.php | Tue Feb 16 15:27:30 2016 +0100 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class GoogleTargetEditor extends OAuthTargetEditor {
protected function initTarget() {
parent::initTarget ();
$this->root = isNull ( $this->settings, $this->target_name, 'root' );
$this->_path_id = $this->settings [$this->target_name . '_path_id'];
if (empty ( $this->_path_id )) {
$this->_path_id = $this->root;
}
if (substr ( $this->_path_id, 0, strlen ( $this->root ) ) != $this->root) {
$this->_path_id = addTrailingSlash ( $this->root, '/' ) . $this->_path_id;
}
$this->_path_id = addTrailingSlash ( $this->_path_id, '/' );
$this->on_read_folder_ready = 'function (){
var table=document.getElementById("folder_content");
if(!table){return;}
var rows=table.getElementsByTagName("TR"),i,file_id;
for(i=0;i<rows.length;i+=1){
file_id=parent.getFakeAttrFromStyle(rows[i], "file_id");
if(null!=file_id)
rows[i].children[1].title="Google Drive file_id : "+file_id;
}
}';
}
protected function getExpertEditorTemplate() {
if (! parent::getExpertEditorTemplate ())
return false;
$help_1 = "'" . sprintf ( _esc ( 'The root relative to which path is specified.<br>Valid values are %s or the Google folder`s <b>file_id</b>.<br>The folder`s <b>file_id</b> is shown by moving the mouse over the folder.<br>You may use use the copy button if you want.' ), '<b>' . GOOGLE_ROOT . '</b>' ) . "'";
$help_2 = "'" . _esc ( 'Not yet enabled' ) . "'";
$help_3 = "'" . sprintf ( _esc ( 'With Google it is possible to download a file directly from your Google Drive account. Of couse, you must first sing-in but somehow you did this when you linked %s with your Google Drive account.<br>This feature is important because you can download without implying the web server (where %s is installed) and such you cut the unwanted round trips (and data transfer) between your local system->%s->Google Drive.<br>With this option you may opt between accessing the file directly or via the webserver. If I were you I would choose to download directly (it`s faster).' ), WPMYBACKUP, WPMYBACKUP, WPMYBACKUP ) . "'";
$help_4 = "''";
$selected = 'selected';
require_once $this->getTemplatePath ( 'google-expert.php' );
}
}
?>