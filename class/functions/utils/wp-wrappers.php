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
 * @file    : wp-wrappers.php $
 * 
 * @id      : wp-wrappers.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function plugins_url_wrapper($path, $plugin)
{
$plugin_root = str_replace(DIRECTORY_SEPARATOR, '/', getFileRelativePath($plugin));
'/' != substr($path, 0, 1) && '/' != substr($plugin_root, - 1) && ($plugin_root .= '/') || ('/' == substr($plugin_root, - 1) && ($plugin_root = substr($plugin_root, 0, - 1)));
$result = function_exists('\\plugins_url') ? plugins_url($path, $plugin) : ($plugin_root . $path);
return $result;
}
function plugin_dir_path_wrapper($file)
{
return function_exists('\\plugin_dir_path') ? plugin_dir_path($file) : dirname($file);
}
function get_option_wrapper($option, $default)
{
return function_exists('\\get_option') ? get_option($option, $default) : get_option_local_db($option, $default);
}
function get_ABSPATH_wrapper()
{
return defined(__NAMESPACE__.'\\ALT_ABSPATH') ? '/' : DIRECTORY_SEPARATOR == substr(__DIR__, - 1) ? substr(__DIR__, 0, strlen(__DIR__)) : __DIR__;
}
function get_home_url_wrapper($blog_id = null, $path = null, $scheme = null)
{
if (function_exists('\\get_home_url'))
return get_home_url($blog_id, $path, $scheme);
else {
$url = selfURL(true);
if ('/' == substr($url, - 1))
return substr($url, 0, strlen($url) - 1);
else
return $url;
}
}
function update_option_wrapper($option, $new_value)
{
return function_exists('\\update_option') ? update_option($option, $new_value) : update_option_local_db($option, $new_value);
}
function delete_option_wrapper($option)
{
return function_exists('\\delete_option') ? delete_option($option) : delete_option_local_db($option);
}
function wp_get_schedules_wrapper()
{
return function_exists('\\wp_get_schedules') ? wp_get_schedules() : array();
}
function wp_get_cron_schedules($schedule_name = null)
{
$result = array();
$schedules = wp_get_schedules_wrapper();
if (empty($schedule_name))
return $schedules;
foreach ($schedules as $scheduled => $schedule_def)
if ($schedule == $schedule_name) {
$result = $schedule_def;
break;
}
return $result;
}
function wp_get_schedule_by_hookname($hook)
{
$schedules = get_option('cron');
if ($schedules)
foreach ($schedules as $timestamp => $cron_jobs) {
if (! (is_array($cron_jobs) && is_numeric($timestamp)))
continue;
if (isset($cron_jobs[$hook]))
return array(
$timestamp => $cron_jobs[$hook]
);
}
return false;
}
function wp_create_nonce_wrapper($action, $force_non_wp = false)
{
return !$force_non_wp && function_exists('\\wp_create_nonce') ? wp_create_nonce($action) : create_nonce($action);
}
function wp_verify_nonce_wrapper($nonce, $action)
{
return function_exists('\\wp_verify_nonce') ? wp_verify_nonce($nonce, $action) : verify_nonce($nonce, $action);
}
function auth_redirect_wrapper()
{
if (isset($_SESSION) && isset($_SESSION['login_redirect']) && $_SESSION['login_redirect'])
return;
$wp_pluggable = @constant('ABSPATH') && @constant('WPINC') ? ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'pluggable.php' : false;
if ($wp_pluggable && is_wp() && _file_exists($wp_pluggable) && function_exists('\\is_user_logged_in') && ! is_user_logged_in()) {
add_session_var('login_redirect', true);
include_once $wp_pluggable;
auth_redirect(); 
} else {
locationRedirect(selfURL(false)); 
}
}
function is_multisite_wrapper()
{
if (function_exists('\\is_multisite'))
return \is_multisite();
else
return false;
}
function get_current_user_id_wrapper()
{
if (is_wp()) {
if (function_exists('\\get_current_user_id'))
return get_current_user_id();
} else {
if (isset($_SESSION) && isset($_SESSION['simple_login_username']))
return $_SESSION['simple_login_username'];
}
return false;
}
function is_wp()
{
return @constant('ABSPATH') && $GLOBALS['wp_version'] && _file_exists(ABSPATH . DIRECTORY_SEPARATOR . 'wp-includes' . DIRECTORY_SEPARATOR . 'version.php') && function_exists('\\add_management_page');
}
function wp_get_timezone_string()
{
if (function_exists('\\get_option') && $timezone = get_option('timezone_string'))
return $timezone;
if (0 === (function_exists('\\get_option') && $utc_offset = get_option('gmt_offset', 0)))
return 'UTC';
else
$utc_offset = 0;
$utc_offset *= 3600;
$timezone = timezone_name_from_abbr('', $utc_offset);
if (false === $timezone) {
$is_dst = date('I');
$timezone = null;
foreach (timezone_abbreviations_list() as $abbr) {
foreach ($abbr as $city) {
if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset) {
$timezone = $city['timezone_id'];
break;
}
}
}
if (! empty($timezone))
return $timezone;
}
return 'UTC';
}
function get_wp_config_path()
{
return is_wp() ? ABSPATH . 'wp-config.php' : FALSE;
}
function update_wp_config($array)
{
global $container_shape;
if (! empty($array)) {
$filename = get_wp_config_path();
$backup = $filename . '.' . time();
$buffer = file_get_contents($filename);
$comment = ' by ' . WPMYBACKUP . ' @ ' . date(DATETIME_FORMAT . ' e');
$summary = array();
foreach ($array as $key => $value) {
$pattern = '/((define\s*\(\s*[\'"]' . $key . '[\'"]\s*,\s*)([\w]+)([^;]+;)).*/';
if (preg_match($pattern, $buffer))
$buffer = preg_replace($pattern, '$2' . $value . '$4 // changed' . $comment . ' << $1', $buffer);
else {
$pattern = '/([\S\s]+)(?=\?>)([\s\S]*)/';
$buffer = preg_replace($pattern, "$1\n" . "define('$key' , $value); // added" . $comment . "\n$2\n", $buffer);
}
$summary[] = "<b>$key</b> = <i>$value</i>";
}
if (copy($filename, $backup) && file_put_contents($filename, $buffer)) {
$summary = ! empty($summary) ? "<ul style=\'list-style: square inside none;\'><li>" . implode('</li><li>', $summary) . '</li></ul>' : '';
return sprintf("parent.popupWindow('%s','%s');", _esc('Confirmation'), sprintf(_esc('The file %s has been updated successfully.%s: the old file has been copied at %s'), addslashes($filename), '<br><br><b>' . _esc('What was changed') . "</b><blockquote class=\'hintbox $container_shape\'>$summary</blockquote><b>" . _esc('Note') . '</b>', addslashes($backup)));
}
}
$err = error_get_last();
$err_str = sprintf(_esc('Could not update the %s.%s'), addslashes($filename), '<br>' . $err['message']);
return sprintf("parent.popupError('%s','%s');", _esc('Error'), $err_str);
}
function wp_get_admin_email()
{
return wp_exec_in_blog(function () {
return \get_bloginfo('admin_email');
});
}
function wp_get_upload_dir()
{
return wp_exec_in_blog(function () {
return \wp_upload_dir();
});
}
function wp_get_db_prefix()
{
return wp_exec_in_blog(function () {
global $wpdb;
return isset($wpdb) ? $wpdb->prefix : '';
});
}
function wp_get_current_blog_id()
{
global $blog_id;
$result = function_exists('\\get_current_blog_id') ? \get_current_blog_id() : $blog_id;
return ! empty($result) ? $result : null;
}
function wp_get_site_id()
{
$site_id = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
if (is_wp()) {
$site_id = wp_get_current_blog_id();
}
return $site_id;
}
function wp_get_plugins($status = 'all')
{
$plugins = array(
'all' => array(),
'active' => array(),
'inactive' => array()
);
if (is_wp()) {
if (! function_exists('\\get_plugins'))
require_once \ABSPATH . 'wp-admin/includes/plugin.php';
$plugins['all'] = \apply_filters('all_plugins', \get_plugins());
foreach ((array) $plugins['all'] as $plugin_file => $plugin_data) {
if (\is_multisite() && \is_network_only_plugin($plugin_file) && ! \is_plugin_active($plugin_file)) {
unset($plugins['all'][$plugin_file]);
} elseif (\is_plugin_active_for_network($plugin_file)) {
unset($plugins['all'][$plugin_file]);
} elseif ((\is_plugin_active($plugin_file)) || (\is_plugin_active_for_network($plugin_file))) {
$plugins['active'][$plugin_file] = $plugin_data;
} else {
$plugins['inactive'][$plugin_file] = $plugin_data;
}
}
}
return $plugins[$status];
}
function wp_exec_in_blog($callback, $args = array(), $blog_id = -1, $default = '', $default_single = true)
{
is_array($args) || $args = array();
$result = $default;
if (function_exists('\\switch_to_blog')) {
(- 1 == $blog_id) && $blog_id = wp_get_current_blog_id();
switch_to_blog($blog_id);
is_callable($callback) && $result = call_user_func_array($callback, $args);
restore_current_blog();
} elseif ($default_single && is_callable($callback))
$result = call_user_func_array($callback, $args);
return $result;
}
function is_administrator()
{
return function_exists('\\current_user_can') && \current_user_can('manage_options');
}
function is_wpmu_admin()
{
return function_exists('\\is_network_admin') && \is_network_admin() && is_wpmu_superadmin();
}
function is_wpmu_superadmin()
{
return \current_user_can('manage_network_options');
}
function wp_get_user_blogs_prefixes($all = false)
{
$result = array();
if (function_exists('\\get_blogs_of_user')) {
foreach (\get_blogs_of_user(\get_current_user_id(), $all) as $blog_id => $blog_info) {
$result[$blog_id] = wp_exec_in_blog(function () {
global $wpdb;
return isset($wpdb) ? $wpdb->prefix : '';
}, null, $blog_id);
}
ksort($result);
} else {
$blog_id = wp_get_current_blog_id();
$result[isset($blog_id) ? $blog_id : 1] = wp_get_db_prefix();
}
return $result;
}
function wp_get_all_blogs_prefixes()
{
return wp_get_user_blogs_prefixes(true);
}
?>