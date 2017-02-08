<?php
/**
 * ################################################################################
 * MyBackup
 * 
 * Copyright 2017 Eugen Mihailescu <eugenmihailescux@gmail.com>
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
 * @version : 1.0-3 $
 * @commit  : 1b3291b4703ba7104acb73f0a2dc19e3a99f1ac1 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Feb 7 08:55:11 2017 +0100 $
 * @file    : regactions.php $
 * 
 * @id      : regactions.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

require_once dirname(__DIR__) . '/config.php';
include_once FUNCTIONS_PATH . 'settings.php';
include_once LOCALE_PATH . 'locale.php';
include_once CLASS_PATH . 'AjaxRequests.php';
ini_set("error_log", ERROR_LOG);
set_exception_handler(function ($e) {
die(sprintf("Unhandled exception : %s (%s:%s)", $e->getMessage(), $e->getFile(), $e->getLine()));
});
define(__NAMESPACE__.'\\WP_AJAX_ACTION_PREFIX', 'wp_ajax_');
define(__NAMESPACE__.'\\WP_MYBACKUP_ACTION_PREFIX', 'wpmybackup_');
define(__NAMESPACE__.'\\AJAX_DISPATCH_FUNCTION', WP_MYBACKUP_ACTION_PREFIX . 'ajax');
setLanguage(getSelectedLangCode());
is_session_started(); 
$settings = loadSettings();
if (! defined(__NAMESPACE__.'\\DO_NOT_AFTER_SETTINGS') || ! DO_NOT_AFTER_SETTINGS)
afterSettingsLoad($settings, true); 
require_once CONFIG_PATH . 'default-target-tabs.php';
include_once CONFIG_PATH . 'ajax-actions.php';
if (isset($_POST['tlid']) && defined(__NAMESPACE__.'\\TARGETLIST_DB_PATH')) {
$class = __NAMESPACE__ . 'TargetListEditor';
$target_list = new TargetCollection(TARGETLIST_DB_PATH);
if (false !== ($target_item = $target_list->getTargetItem($_POST['tlid'])))
$settings = $target_item->targetSettings; 
elseif ((isset($_SESSION['id']) && $_SESSION['id'] == $_POST['tlid']) || isset($_SESSION['edit_step']) && isset($_SESSION['edit_step']['sender']) && $class == $_SESSION['edit_step']['sender'] && isset($_SESSION['edit_step']['step_data'])) {
$array = json_decode(str_replace('\"', '"', $_SESSION['edit_step']['step_data']), true);
is_array($array) && $settings = array_merge($settings, $array);
}
}
$ajax_request = new AjaxRequests($settings);
$actions = array();
$is_wp = is_wp();
if (! $is_wp) {
function add_action($action, $callback)
{
global $actions;
$action = str_replace(WP_AJAX_ACTION_PREFIX . '', '', $action);
if (! key_exists($action, $actions))
$actions[$action] = $callback;
}
}
foreach (get_valid_ajax_actions() as $ajax_action) {
add_action(WP_AJAX_ACTION_PREFIX . $ajax_action, array(
$ajax_request,
AJAX_DISPATCH_FUNCTION
));
}
add_action('admin_init', array(
$ajax_request,
WP_MYBACKUP_ACTION_PREFIX . 'do_action'
));
add_action('init', array(
$ajax_request,
WP_MYBACKUP_ACTION_PREFIX . 'do_action'
));
if (! $is_wp && isset($_POST) && isset($_POST['action'])) {
$err_pattern = sprintf(_esc("Action '%%s' %%s.<br>This should never happen. Please <a href='%s'>report this issue</a>"), getReportIssueURL()) . ".<br><a onclick='history.back();' style='cursor:pointer'>&lt;&lt; " . _esc('Back') . "</a>";
$action_found = false;
foreach ($actions as $action => $callback) {
if ($action == $_POST['action'])
if (method_exists($callback[0], AJAX_DISPATCH_FUNCTION)) {
try {
$action_found = true;
_call_user_func($callback);
} catch (\Exception $e) {
die($e->getMessage());
}
} else {
die(sprintf($err_pattern, $action, _esc('is badly constructed (where is ') . get_class($callback[0]) . '::' . AJAX_DISPATCH_FUNCTION . ' ?)'));
}
}
if (isset($actions['init']))
try {
$action_found = $action_found || _call_user_func($actions['init']);
} catch (\Exception $e) {
die($e->getMessage());
}
if (! $action_found)
die(sprintf($err_pattern, $_POST['action'], _esc('is not declared')));
}
?>