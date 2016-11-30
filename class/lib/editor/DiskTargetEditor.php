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
 * @version : 0.2.3-33 $
 * @commit  : 8322fc3e4ca12a069f0821feb9324ea7cfa728bd $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Nov 29 16:33:58 2016 +0100 $
 * @file    : DiskTargetEditor.php $
 * 
 * @id      : DiskTargetEditor.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class DiskTargetEditor extends AbstractTargetEditor {
protected function initTarget() {
parent::initTarget ();
$this->hasInfoBanner = defined(__NAMESPACE__.'\\FILE_EXPLORER');
}
protected function hideEditorContent() {
return ! (_is_dir ( $this->root ) && $this->enabled);
}
protected function onGenerateEditorContent() {
$java_scripts = echoFolder ( $this->target_name, $this->root, $this->root, $this->ext_filter, $this->function_name, DIRECTORY_SEPARATOR, null, $this->folder_style, false, null, $this->settings );
$this->java_scripts = array_merge ( $this->java_scripts, $java_scripts );
}
protected function getEditorTemplate() {
$help_1 = "'"._esc('Keep only the last n-days backups on Disk.<br>Leave it empty to disable this option')."'";
require_once $this->getTemplatePath ( 'diskdst.php' );
}
protected function getHomeDir() {
return addTrailingSlash ( getUserHomeDir () );
}
protected function getHomeFolderJS() {
return "document.getElementsByName('{$this->target_name}')[0].value='" . normalize_path( $this->getHomeDir () ) . "';var el=document.getElementById('update_{$this->target_name}_dir');if(el)el.click();";
}
}
?>