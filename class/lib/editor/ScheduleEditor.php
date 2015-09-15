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
 * @file    : ScheduleEditor.php $
 * 
 * @id      : ScheduleEditor.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
class ScheduleEditor extends AbstractTargetEditor {
protected $_schedules;
protected $_options_help;
protected $_is_oscron_disabled;
protected function _encloseHelpLink($str) {
if (isset ( $this->_options_help [$str] ))
$onclick = "onclick=\'js55f82caaae905.popupWindow(&quot;Help $str&quot;,&quot;{$this->_options_help[$str]}&quot;);\'";
else
$onclick = '';
return '<span class=\'help schedule_param\' ' . $onclick . '>' . $str . '</span>';
}
protected function _echoParam($name, $long_name, $include_empty = false, $default = null, $skip_value = false, $dependson = null, $quote = false, $color = null, $getval_clbk = null) {
$value = _is_callable ( $getval_clbk ) ? _call_user_func ( $getval_clbk ) : $this->settings [$name];
$q = $quote ? '\"' : '';
if (! empty ( $dependson ) && '1' != $this->settings [$dependson])
return;
if (! empty ( $color )) {
$cs = "<span style='color:$color;'>";
$ce = '</span>';
} else {
$cs = '';
$ce = '';
}
if (! empty ( $value ) || $include_empty || ! empty ( $default ))
echo ' <b>--' . $this->_encloseHelpLink ( $long_name );
if (! $skip_value && (! empty ( $value ) || ! empty ( $default )))
echo '=</b>' . $q . $cs . (empty ( $value ) ? (empty ( $default ) ? '' : $default) : addslashes ( $value )) . $ce . $q;
else
echo '</b>';
}
protected function initTarget() {
parent::initTarget ();
$this->hasCustomFrame = true;
$this->_schedules = array ();
$this->_options_help = array ();
$this->_is_oscron_disabled = true;
}
protected function getEditorTemplate() {
global $REGISTERED_SCHEDULE_TABS;
$sel_tab = getSelectedTab ();
$tab_link = getTabLink ( $sel_tab );
$tab_link = stripUrlParams ( $tab_link, array (
'gr' 
) );
$tabs = array ();
foreach ( $REGISTERED_SCHEDULE_TABS as $schedule_type => $schedule_name )
$tabs [$schedule_type] = $schedule_name . '-Cron';
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
if (! isset ( $REGISTERED_SCHEDULE_TABS [$group] )) {
if (false !== ($include_tab_file = chkIncludeTab ( $tabs, $group, basename ( CUSTOM_PATH ) . DIRECTORY_SEPARATOR . 'targets' )))
require_once $this->getTemplatePath ( $include_tab_file, CUSTOM_PATH );
} else
echoTargetEditor ( $group );
echo '</div>' . PHP_EOL;
}
}
?>
