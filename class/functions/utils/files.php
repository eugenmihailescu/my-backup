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
 * @file    : files.php $
 * 
 * @id      : files.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

define(__NAMESPACE__.'\\DIR_SIZE_CACHE_FILE', addTrailingSlash(LOG_DIR) . "disk.cache");
function getFileListByExt($dir, $include_ext)
{
$dir = addTrailingSlash($dir);
$dh = opendir($dir);
$files = array();
while (false !== ($filename = readdir($dh))) {
$fullPath = $dir . $filename;
if ($filename != "." && $filename != "..")
if (! _is_dir($fullPath)) {
$p = strrpos($filename, ".");
if (false !== $p && substr($filename, $p + 1) === $include_ext)
$files[] = $fullPath;
}
}
closedir($dh);
return $files;
}
function getFileListByPattern($dir, $pattern, $recursively = false, $add_empty_dir = true, $tree = true, $output_style = false, $skip_files = null, $skip_links = true)
{
$dir .= DIRECTORY_SEPARATOR != substr($dir, - 1) ? DIRECTORY_SEPARATOR : '';
if (! _file_exists($dir)){
throw new MyException(sprintf(_esc("File or directory %s does not exists"), $dir));
}
$dh = @opendir($dir);
($tree && $dirname = basename($dir)) || $dirname = $dir;
($tree && $files = array(
$dirname => array()
)) || ($files = 2 == $output_style ? array() : array(
$dirname
));
if (false !== $dh) {
$has_skip_files = is_array($skip_files);
while (false !== ($filename = @readdir($dh))) {
if ($filename == "." || $filename == "..")
continue;
$fullPath = $dir . $filename;
if (($has_skip_files && in_array($fullPath, $skip_files)) || ($skip_links && is_link($fullPath)))
continue;
if (! _is_dir($fullPath)) {
if (1 != $output_style && (null == $pattern || preg_match($pattern, $fullPath)))
($tree && $files[$dirname][] = $filename) || $files[] = $dirname . $filename;
} elseif ($recursively) {
$children = getFileListByPattern($fullPath, $pattern, $recursively, $add_empty_dir, $tree, $output_style, $skip_files, $skip_links);
if ($add_empty_dir || 1 == $output_style || ! empty($children))
($tree && $files[$dirname] = array_merge($children, $files[$dirname])) || $files = array_merge($children, $files);
}
}
closedir($dh);
}
return $add_empty_dir || ! empty($files) ? $files : '';
}
function getFilesTime($files, $pattern)
{
$dates = array();
if (null != $files)
foreach ($files as $filename) {
$subject = formatRegEx(array(
chr(92),
".",
"^",
"$",
"+",
"-",
"(",
")",
"[",
"]",
"{",
"}"
), "@", $pattern) . "([0-9]{8,}\-[0-9]{6,}).*";
if (preg_match("@" . $subject . "@", $filename, $matches)) {
$date = \DateTime::createFromFormat('Ymd-His', $matches[1])->getTimestamp();
if (! in_array($date, $dates))
$dates[] = $date;
}
}
return $dates;
}
function getFilesSize($files)
{
$result = 0;
foreach ($files as $filename)
! empty($filename) && _is_file($filename) && $result += @filesize($filename);
return $result;
}
function createFileList($temp_file, $dir, $exclude_dirs = null, $exclude_ext = null, $exclude_files = null, $exclude_links = true, $callback = null)
{
$fcount = 0;
if (is_array($dir)) {
$preg_separator = '/';
$excluded_prefixes = array();
$pattern = '';
$preg_esc = function (&$array, $_is_dir = false, $escape = false) use (&$preg_separator) {
$array = array_unique($array);
array_walk($array, function (&$item) use (&$preg_separator, &$_is_dir, &$escape) {
$_is_dir && $item = delTrailingSlash($item);
$escape && $item = preg_quote($item, $preg_separator);
});
return true;
};
$put_temp_file = function ($path, $array) use (&$temp_file, &$callback) {
$count = count($array);
if ($count && file_put_contents($temp_file, implode(PHP_EOL, $array) . PHP_EOL, FILE_APPEND)) {
if (is_array($callback) && _is_callable($callback[2]))
_call_user_func($callback[2], sprintf(_esc("Added %s directory (%s files)"), $path, $count), BULLET, 1);
return $count;
}
return false;
};
$get_files_by_pattern = function ($fullPath, $recursive = true) use (&$pattern, &$exclude_files, &$exclude_links, &$preg_separator, &$callback, &$put_temp_file) {
$is_link = is_link($fullPath);
$fullPath = realpath($fullPath);
if (! _file_exists($fullPath))
return 0;
if (! $is_link || ! $exclude_links) {
$file_list = getFileListByPattern($fullPath, '.*' == $pattern ? null : ($preg_separator . $pattern . $preg_separator), $recursive, false, false, 2, $exclude_files, $exclude_links);
$count = count($file_list);
} elseif (is_array($callback) && _is_callable($callback[2])) {
_call_user_func($callback[2], '<yellow>' . sprintf(_esc('Skipping the file link %s'), $fullPath) . '</yellow>');
return 0;
}
return ! empty($file_list) ? $put_temp_file($fullPath, $file_list) : 0;
};
$array_to_pattern = function ($array ) use(&$preg_esc, &$preg_separator) {
uasort($array, function ($a, $b) {
return strlen($a) - strlen($b);
});
$pattern = '';
$fitem = array_pop($array);
$root = '';
foreach (str_split($fitem) as $i => $c) {
$changed = false;
foreach ($array as $item) {
if ($changed = $c != $item[$i])
break;
}
if ($changed) {
$root = substr($fitem, 0, $i);
break;
}
}
! $root && $root = $fitem;
if ($root) {
array_walk($array, function (&$item) use (&$root, &$preg_separator) {
$item = preg_replace($preg_separator . '^' . preg_quote($root, $preg_separator) . $preg_separator, '', $item);
});
}
$preg_esc($array, false, true);
return sprintf('%s%s', preg_quote($root, $preg_separator), count($array) ? '(' . implode('|', $array) . ')' : '');
};
if (is_array($exclude_dirs)) {
$filter_subfiles = function ($array) use (&$exclude_dirs) {
return array_filter($array, function ($item) use (&$exclude_dirs) {
$keep = ! empty($item) && _is_dir($item);
if ($keep)
foreach ($exclude_dirs as $d)
if ($item != $d && ! ($keep = 0 !== strpos($item, $d)))
break;
return $keep;
});
};
$exclude_dirs = $filter_subfiles($exclude_dirs);
is_array($exclude_files) && $exclude_files = $filter_subfiles($exclude_files);
$preg_esc($exclude_dirs, true) && $excluded_prefixes = array_merge($excluded_prefixes, $exclude_dirs);
}
is_array($exclude_files) && $preg_esc($exclude_files) && $excluded_prefixes = array_merge($excluded_prefixes, $exclude_files);
count($excluded_prefixes) && $pattern .= '^(?!(' . $array_to_pattern($excluded_prefixes) . '))';
$pattern .= '.*';
$dir = array_filter($dir, function ($item) use (&$pattern, &$preg_separator) {
return preg_match($preg_separator . $pattern . $preg_separator, $item);
});
is_array($exclude_ext) && ! empty($exclude_ext) && $preg_esc($exclude_ext) && $pattern .= '\.?.+(?<!.' . implode('|', $exclude_ext) . ')$';
$scan_dirs = array();
foreach ($dir as $key => $dirname) {
$has_children = false;
foreach ($dir as $k => $d) {
if (($key != $k) && (0 === strpos($d, $dirname))) {
$has_children = true;
break;
}
}
$scan_dirs[$dirname] = $has_children;
}
$p = 1;
$max_p = count($scan_dirs);
foreach ($scan_dirs as $fullPath => $has_children) {
if (is_array($callback) && _is_callable($callback[0]) && _call_user_func($callback[0]))
break;
if (is_array($callback) && _is_callable($callback[1]))
_call_user_func($callback[1], TMPFILE_SOURCE, $temp_file, $p ++, $max_p, 2);
$fcount += $get_files_by_pattern($fullPath, ! $has_children);
}
} else
throw new MyException(_esc('createFileList : $dir is not array. This should never happen.'));
return $fcount;
}
function createFileListBySections($temp_file, $wp_components, $callbacks = null)
{
if (! ($fr = fopen($temp_file, 'r')))
return false;
$min_len = isWin() ? 3 : strlen(DIRECTORY_SEPARATOR);
$wp_components_old = $wp_components;
$section_name = function ($filename) use (&$wp_components_old, &$min_len) {
_is_file($filename) && $filename = dirname($filename);
$found = false;
while (isset($filename[$min_len]) && ! ($found = in_array($filename, $wp_components_old)))
$filename = dirname($filename);
return $found ? basename($filename) : null;
};
$has_callbacks = is_array($callbacks);
$has_abort_callback = $has_callbacks && _is_callable($callbacks['abort']);
$has_progress_callback = $has_callbacks && _is_callable($callbacks['progress']);
$max_i = getFileLinesCount($temp_file);
$processed = array();
$result = array();
$section_temp_files = array();
$wp_components[''] = ''; 
$p = 1;
$max_p = count($wp_components);
$eol_len = strlen(PHP_EOL);
$fwh = array();
$lines = array();
foreach ($wp_components as $index => $section) {
$wp_components[$index] = basename($section);
$section_temp_files[$wp_components[$index]] = array(
'file' => $temp_file . '.' . (empty($section) ? uniqid() : $wp_components[$index]),
'section' => $section
);
if (! ($fwh[$wp_components[$index]] = fopen($section_temp_files[$wp_components[$index]]['file'], 'w')))
continue;
$lines[$wp_components[$index]] = 0;
}
$current_section = '';
$old_perc = 0;
$new_perc = 0.1;
$i = 0;
while (false !== ($filename = fgets($fr))) {
$i ++;
if ($has_abort_callback && _call_user_func($callbacks['abort']))
break;
$has_eol = PHP_EOL == substr($filename, - $eol_len);
$has_eol && $filename = substr($filename, 0, - $eol_len);
if ($has_progress_callback && $old_perc != $new_perc) {
_call_user_func($callbacks['progress'], TMPFILE_SOURCE, $temp_file, $i, $max_i, 2);
$old_perc = $new_perc;
}
$new_perc = ceil(100 * $i / $max_i);
if (empty($filename))
continue;
$current_section = $section_name($filename);
if (! in_array($current_section, $wp_components))
continue;
$crc32 = crc32($filename);
if (in_array($crc32, $processed)) {
continue;
}
if ($fwh[$current_section])
$lines[$current_section] += false !== fwrite($fwh[$current_section], $filename . PHP_EOL);
$processed[] = $crc32;
}
$has_progress_callback && _call_user_func($callbacks['progress'], TMPFILE_SOURCE, $temp_file, $max_i, $max_i, 2);
foreach ($fwh as $section => $fw) {
$fw && fclose($fw);
$lines[$section] && ($result[$section_temp_files[$section]['file']] = array(
'section' => $section_temp_files[$section]['section'],
'lines' => $lines[$section]
)) || unlink($section_temp_files[$section]['file']);
}
fclose($fr);
ksort($result);
return $result;
}
function createDirList($dir, $level = null)
{
$dir = addTrailingSlash($dir);
$dh = @opendir($dir);
if (null != $level && $level < 0 || '/dev/' == $dir) 
return 0;
$result = array();
if (_is_dir($dir) && $dh) {
while (false !== ($filename = readdir($dh))) {
$fullPath = $dir . $filename;
if ($filename != "." && $filename != "..")
if (_is_dir($fullPath))
$result = array_merge($result, createDirList($fullPath, null != $level ? $level - 1 : $level));
}
empty($result) && $result[] = $dir;
closedir($dh);
}
return $result;
}
function getAbstractDirSize($folder, $excl_dirs = null, $recursive = true)
{
if (! _dir_in_allowed_path($folder))
return false;
$files = @scandir($folder);
if (! is_array($files))
return false;
$dir = addTrailingSlash($folder);
$dir_size = 0;
foreach ($files as $f)
if ('.' != substr($f, 0, 1)) {
try {
$currentFile = $dir . $f;
if (_is_dir($currentFile)) {
$match = false;
if (is_array($excl_dirs))
foreach ($excl_dirs as $excl_dir)
$match = $match || false !== strpos($currentFile, $excl_dir);
! $match && $dir_size += $recursive ? getAbstractDirSize($currentFile, $excl_dirs) : 0;
} elseif (! (@is_link($currentFile) && '.' == @readlink($currentFile))) {
$dir_size += @filesize($currentFile);
}
} catch (\Exception $e) {}
}
return $dir_size;
}
function unixGetDirSize($folder, $excl_dirs = null)
{
$excl_pattern = getTarExclusionPattern($folder, null, $excl_dirs);
$io = @popen(sprintf('du -sB1KB %s %s 2>/dev/null', $folder, $excl_pattern), 'r');
if (! $io)
return getAbstractDirSize($folder, $excl_dirs);
$KB = 1000; 
$size = $KB * fgets($io, 4096);
pclose($io);
if (preg_match('/^\d*/', $size, $matches))
return $matches[0];
else
return false;
}
function winGetDirSize($folder, $excl_dirs = null)
{
if (class_exists('\\COM')) {
try {
$fs = new \COM('Scripting.FileSystemObject');
if (is_object($fs)) {
if (is_array($excl_dirs) && in_array($folder, $excl_dirs))
return 0;
$specs = $fs->GetFolder($folder);
$size = $specs->Size;
$fs = null;
return $size;
}
} catch (\Exception $e) {}
}
return false;
}
function getDirSize($folder, $excl_dirs = null)
{
if (isWin()) {
return class_exists('\\COM') ? winGetDirSize($folder, $excl_dirs) : getAbstractDirSize($folder, $excl_dirs);
} else
return unixGetDirSize($folder, $excl_dirs);
}
function getDirCacheSize()
{
if (_file_exists(DIR_SIZE_CACHE_FILE))
return filesize(DIR_SIZE_CACHE_FILE);
else
return 0;
}
function clearDirSizeCache()
{
if (_file_exists(DIR_SIZE_CACHE_FILE))
unlink(DIR_SIZE_CACHE_FILE);
}
function getDirSizeFromCache($folder, $cache_only = false)
{
if (_file_exists(DIR_SIZE_CACHE_FILE) && $files = json_decode(file_get_contents(DIR_SIZE_CACHE_FILE), true))
if (is_array($files) && isset($files[$folder]) && $size = $files[$folder])
return $size;
if ($cache_only)
return 0;
$size = getDirSize($folder);
$files[$folder] = $size;
file_put_contents(DIR_SIZE_CACHE_FILE, json_encode($files, 64)); 
return $size;
}
function getDirSizeByFileList($temp_file)
{
if (! _file_exists($temp_file) || false === ($fr = fopen($temp_file, 'r')))
return false;
$result = 0;
while (false !== ($filename = fgets($fr))) {
$filename = str_replace(PHP_EOL, '', $filename);
_file_exists($filename) && $result += filesize($filename);
}
fclose($fr);
return $result;
}
function addTrailingSlash($path, $separator = DIRECTORY_SEPARATOR, $escape_separator = false)
{
return (empty($path) || substr($path, - 1) != $separator) ? $path . ($escape_separator ? addslashes($separator) : $separator) : $path;
}
function delTrailingSlash($path, $separator = DIRECTORY_SEPARATOR)
{
$len = strlen($separator);
if (substr($path, - $len) == $separator)
return substr($path, 0, - $len);
else
return $path;
}
function getFileRelativePath($filename)
{
$relpath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', $filename);
DIRECTORY_SEPARATOR != substr($relpath, 0, 1) && $relpath = DIRECTORY_SEPARATOR . $relpath; 
return dirname($relpath);
}
function tailFile($filepath, $lines = 1)
{
$f = @fopen($filepath, "rb");
if ($f === false)
return false;
fseek($f, - 1, SEEK_END);
if (fread($f, 1) != "\n")
$lines -= 1;
$output = '';
$chunk = '';
while (ftell($f) > 0 && $lines >= 0) {
$seek = min(ftell($f), 4096);
fseek($f, - $seek, SEEK_CUR);
$output = ($chunk = fread($f, $seek)) . $output;
fseek($f, - strlen($chunk), SEEK_CUR);
$lines -= substr_count($chunk, "\n");
}
while ($lines ++ < 0) {
$output = substr($output, strpos($output, "\n") + 1);
}
fclose($f);
return trim($output);
}
function monitorFile($filepath, $interval = 1, $read_callback, $abort_callback = null)
{
_is_callable($read_callback) || die(_esc('Function monitorFile says: $callback is not a callable function'));
$size = 0;
while (true) {
if (_is_callable($abort_callback) && _call_user_func($abort_callback)) {
_pesc('Abort signal received. File monitoring aborted.');
break;
}
clearstatcache();
_file_exists($filepath) && $newSize = filesize($filepath);
if ($size == $newSize) {
_sleep($interval);
continue;
}
$fh = fopen($filepath, "rb");
! $fh && ($err = error_get_last()) && die($err['message']);
fseek($fh, $size);
while (! feof($fh))
_call_user_func($read_callback, fread($fh, 8192));
fclose($fh);
$size = $newSize;
}
}
function _dirname($dir, $sep = '/')
{
$result = explode($sep, $dir);
$result = array_filter($result, function ($e) {
return ! empty($e);
});
count($result) > 0 && $result = array_slice($result, 0, count($result) - 1);
return ('/' == $sep ? $sep : '') . implode($sep, $result);
}
function _basename($file, $sep = '/')
{
$result = explode($sep, $file);
$result = array_filter($result, function ($e) {
return ! empty($e);
});
return end($result);
}
function copy_folder($src, $dst)
{
if (! _file_exists($src))
return false;
$src = addTrailingSlash($src);
$dst = addTrailingSlash($dst);
if ($src == $dst)
return true;
$result = true;
! _file_exists($dst) && @mkdir($dst, 0770, true);
$dir = opendir($src);
while (false !== ($file = readdir($dir)))
if (($file != '.') && ($file != '..')) {
if (_is_dir($src . $file)) {
if (addTrailingSlash($src . $file) == $dst)
continue; 
$result = $result && copy_folder($src . $file, $dst . $file);
} else {
$result = $result && copy($src . $file, $dst . $file);
}
}
closedir($dir);
return $result;
}
function normalize_path($path, $reverse = false, $count = 1)
{
$from = '\\';
$to = str_repeat($from, ($count + 1));
$reverse && swap_items($from, $to) || $path = normalize_path($path, true);
return str_replace($from, $to, $path);
}
function getUserHomeDir()
{
if (function_exists('\\posix_getpwuid')) {
$uid = posix_getpwuid(posix_getuid());
$home = $uid['dir'];
} else
$home = getenv('HOME');
if (empty($home))
if (! empty($_SERVER['HOMEDRIVE']) && ! empty($_SERVER['HOMEPATH']))
$home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
if (empty($home) && ! empty($_SERVER['USERPROFILE']))
$home = $_SERVER['USERPROFILE'];
return $home;
}
function splitFile($filename, $vol_size)
{
$volumes = array();
$buff_size = 8192;
if (_file_exists($filename)) {
$remaining = filesize($filename);
$fr = fopen($filename, 'rb');
if (false !== $fr)
while ($remaining > 0) {
$volumes[] = sprintf('%s.split-%d', $filename, count($volumes));
$fw = fopen(end($volumes), 'wb');
$actual = 0;
while (! feof($fr) && $remaining > 0 && $actual + $buff_size < $vol_size) {
$buff = fread($fr, $buff_size);
$byte_read = strlen($buff);
$actual += $byte_read;
$remaining -= $byte_read;
fwrite($fw, $buff);
}
fclose($fw);
}
fclose($fr);
}
return $volumes;
}
function getFileLinesCount($filename, $line_sep = PHP_EOL)
{
$linecount = 0;
if (! _file_exists($filename))
return $linecount;
$handle = fopen($filename, "r");
if (false !== $handle)
while (! feof($handle)) {
$buff = fread($handle, 4096);
false !== $buff && $linecount += substr_count($buff, $line_sep);
}
fclose($handle);
return $linecount;
}
function file_checksum($filename, $attempt_os_first = false)
{
$result = false;
$file_exist = _is_file($filename);
if ($file_exist && $attempt_os_first && ! preg_match('/^win/i', PHP_OS)) {
exec("md5sum $filename", $output, $result);
if (0 == $result && count($output) > 0 && preg_match('/([\d\w]+)?/', $output[0], $matches))
return $matches[1];
else {
exec("md5 -q $filename", $output, $result);
if (0 == $result && count($output) > 0 && preg_match('/([\d\w]+)?/', $output[0], $matches))
return $matches[1];
}
}
return $file_exist ? md5_file($filename) : false;
}
function move_file($source, $dest)
{
$is_win = isWin();
$rename = ! $is_win || strtolower(substr($source, 0, 1)) == strtolower(substr($dest, 0, 1));
return $rename ? rename($source, $dest) : (copy($source, $dest) && unlink($source));
}
function _rmdir($dir, $recursive = true)
{
if (! _file_exists($dir))
return;
$files = getFileListByPattern($dir, '/.+/', $recursive, true, false);
rsort($files);
array_walk($files, function ($item, $key) {
_is_dir($item) && rmdir($item) || unlink($item);
});
}
if (! function_exists('\\sanitize_file_name')) {
function sanitize_file_name($filename)
{
$special_chars = array(
"?",
"[",
"]",
"/",
"\\",
"=",
"<",
">",
":",
";",
",",
"'",
"\"",
"&",
"$",
"#",
"*",
"(",
")",
"|",
"~",
"`",
"!",
"{",
"}",
"%",
"+",
chr(0)
);
$filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
$filename = str_replace($special_chars, '', $filename);
$filename = str_replace(array(
'%20',
'+'
), '-', $filename);
$filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
$filename = trim($filename, '.-_');
$parts = explode('.', $filename);
if (count($parts) <= 2) {
return $filename;
}
$filename = array_shift($parts);
$extension = array_pop($parts);
foreach ((array) $parts as $part) {
$filename .= '.' . $part;
}
$filename .= '.' . $extension;
return $filename;
}
}
function get_restricted_functions()
{
$used_functions = array(
'chown',
'curl_exec',
'disk_total_space',
'disk_free_space',
'exec',
'fputs',
'ftp_connect',
'ftp_exec',
'ftp_login',
'ftp_nb_fput',
'ftp_raw',
'ftp_rawlist',
'getmypid',
'mysql_pconnect',
'php_uname',
'popen',
'posix_getpwuid',
'proc_get_status',
'proc_close',
'proc_open',
'popen',
'putenv',
'set_time_limit',
'sleep',
'set_time_limit',
'system'
);
$disable_functions = explode(',', ini_get('disable_functions'));
return array_intersect($used_functions, $disable_functions);
}
function function_is_restricted($function_name)
{
return in_array($function_name, get_restricted_functions());
}
function _disk_free_space($directory)
{
return function_is_restricted('disk_free_space') || ! _is_dir($directory) ? PHP_INT_MAX : disk_free_space($directory);
}
function _disk_total_space($directory)
{
return function_is_restricted('disk_total_space') || ! _is_dir($directory) ? PHP_INT_MAX : disk_total_space($directory);
}
function _is_dir($filename, $function = 'is_dir')
{
$open_basedir = defined(__NAMESPACE__.'\\OPEN_BASEDIR') ? OPEN_BASEDIR : ini_get('open_basedir');
if (empty($open_basedir)) {
return $function($filename);
}
$allowed = _dir_in_allowed_path($filename);
if (! (@is_link($filename) && '.' == @readlink($filename)))
return $allowed && $function($filename);
return false;
}
function _is_file($filename)
{
return _is_dir($filename, 'is_file');
}
function _file_exists($filename)
{
return _is_dir($filename, 'file_exists');
}
function shorten_path($path, $replace_what = ALT_ABSPATH, $with_what = 'ROOT')
{
return str_replace($replace_what, $with_what . DIRECTORY_SEPARATOR, $path);
}
function buildFileList($temp_file, $settings, $src_dir, $excl_dirs, $excl_files, $excl_ext, $excl_links, $verbosity, $callbacks, $force_path = false)
{
$is_wp = is_wp();
if (! $force_path && $is_wp) {
include_once EDITOR_PATH . 'file-functions.php';
$wp_dirs = getWPSourceDirList(WPMYBACKUP_ROOT);
$wp_components = array_keys($wp_dirs);
array_walk($wp_components, function (&$item, $key) {
! empty($item) && $item = WPMYBACKUP_ROOT . $item;
});
} else
$wp_components = array();
$has_output_callback = isset($callbacks) && isset($callbacks['output']) && _is_callable($callbacks['output']);
foreach ($excl_dirs as $excl_dir) {
foreach ($wp_components as $key => $wp_comp) {
if (false !== strpos($wp_comp, $excl_dir)) {
$has_output_callback && _call_user_func($callbacks['output'], _esc('excluding directory ') . shorten_path($wp_components[$key]), BULLET);
unset($wp_components[$key]);
}
}
}
if (isNull($settings, 'plugin_backup', false))
$dir_list = array(
isNull($settings, 'dir', '')
);
else {
include_once EDITOR_PATH . 'file-functions.php';
$dir_list = _call_user_func($is_wp && ! $force_path ? 'getWPDirList' : 'getDirList', $is_wp && ! $force_path ? ALT_ABSPATH : $src_dir, false, 2, false);
$dir_list = array_filter($dir_list, function ($item) use (&$src_dir) {
return 0 === strpos($item, $src_dir);
});
if ($force_path || ! is_multisite_wrapper() || ($is_wp && is_wpmu_superadmin()))
$dir_list = array_merge($dir_list, array(
$src_dir
));
}
$_callbacks = isset($callbacks) ? array(
$callbacks['abort'],
$callbacks['progress'],
$verbosity ? $callbacks['output'] : null
) : null;
$file_count = createFileList($temp_file, $dir_list, $excl_dirs, $excl_ext, $excl_files, $excl_links, $_callbacks);
isset($callbacks) && isset($callbacks['before_section']) && _is_callable($callbacks['before_section']) && _call_user_func($callbacks['before_section'], $temp_file, $file_count, $callbacks);
$has_output_callback && _call_user_func($callbacks['output'], _esc("Organizing the file list into sections"), null, 0);
return array(
$file_count,
createFileListBySections($temp_file, $wp_components, $callbacks)
);
}
?>