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
 * @file    : BackupTargetsEditor.php $
 * 
 * @id      : BackupTargetsEditor.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class BackupTargetsEditor extends AbstractTargetEditor {
protected function initTarget() {
parent::initTarget ();
$this->hasCustomFrame = true;
}
protected function getEditorTemplate() {
global $registered_targets, $TARGET_NAMES, $REGISTERED_BACKUP_TABS;
$sel_tab = getSelectedTab ();
$tab_link = getTabLink ( $sel_tab );
$tab_link = stripUrlParams ( $tab_link, array (
'gr' 
) );
$tabs = array ();
foreach ( $REGISTERED_BACKUP_TABS as $target_type => $target_name )
$tabs [$target_name] = $registered_targets [$target_type] ['title'];
$tab_keys = array_keys ( $tabs );
$group = getSelectedTabGrp ( $tab_keys [0] );
echo '<div id="tab-container1" class="tab-container horizontal hrounded-top"><ul id="navlist1" style="width: 100%; padding-left: 20px; float: none">';
foreach ( $tabs as $tab => $name ) {
$class = ($tab == $group) ? ' active' : '';
$href = $tab_link . '&gr=' . $tab;
echo "<li class='$class' style='margin: 0 2px;border-left:1px solid;border-right:1px solid;'><a href='" . $href . "' style='padding-top:5px;padding-bottom:5px;'>$name</a>";
}
echo '</ul></div>';
echo '<div id="content-container1" class="content-container horizontal ' . $this->container_shape . '" style="min-height: 500px;">';
if (! in_array ( $group, $REGISTERED_BACKUP_TABS )) {
if (false !== ($include_tab_file = chkIncludeTab ( $tabs, $group, basename ( CUSTOM_PATH ) . DIRECTORY_SEPARATOR . 'targets' )))
require_once $this->getTemplatePath ( $include_tab_file, CUSTOM_PATH );
} else
echoTargetEditor ( $group );
echo '</div>' . PHP_EOL;
}
}
?>