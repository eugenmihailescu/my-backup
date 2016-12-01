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
 * @version : 0.2.3-36 $
 * @commit  : c4d8a236c57b60a62c69e03c1273eaff3a9d56fb $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Thu Dec 1 04:37:45 2016 +0100 $
 * @file    : srcfiles_recalc_cache.php $
 * 
 * @id      : srcfiles_recalc_cache.php | Thu Dec 1 04:37:45 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

$cache_file = LOG_PREFIX . '-srcfiles.cache';
if ( ! ( BACKUP_MODE_FULL == $_this_->settings['mode'] && isset( $_this_->settings['use_cache_preload'] ) &&
strToBool( $_this_->settings['use_cache_preload'] ) ) ) {
if ( _is_file( $cache_file ) ) {
if ( $cache_data = json_decode( file_get_contents( $cache_file ), true ) ) {
if ( isset( $cache_data['filename'] ) )
foreach ( array_keys( $cache_data['filename'] ) as $filename ) {
_is_file( $filename ) && @unlink( $filename );
}
}
@unlink( $cache_file );
}
return $result;
}
global $COMPRESSION_NAMES, $exclude_files_factory;
if ( is_file( $cache_file ) ) {
if ( $cache_data = json_decode( file_get_contents( $cache_file ), true ) ) {
if ( $cache_data['running'] || ( $cache_data['done'] &&
time() - $cache_data['timestamp'] < 60 * intval( isNull( $_this_->settings, 'cache_preload_age', 1440 ) ) ) ) {
add_alert_message( _esc( 'The selected Source Files cache done or in progress' ) );
return;
}
}
}
$start = time();
$cache_data = array( 'timestamp' => time(), 'done' => false, 'running' => true ); 
file_put_contents( $cache_file, json_encode( $cache_data ) ); 
$wrkdir = getParam( $_this_->settings, 'wrkdir', _sys_get_temp_dir() );
$wrkdir = addTrailingSlash( $wrkdir );
$src_dir = getParam( $_this_->settings, 'dir', __DIR__ );
$src_dir = addTrailingSlash( $src_dir );
$excl_dirs = ( $o = getParam( $_this_->settings, "excludedirs" ) ) ? explode( ",", $o ) : array();
$excl_dirs = array_map( __NAMESPACE__ . '\\delTrailingSlash', $excl_dirs );
$excl_dirs = array_filter( $excl_dirs, function ( $item ) {
return ! empty( $item );
} );
$excl_ext = ( $o = getParam( $_this_->settings, "excludeext", implode( ',', $COMPRESSION_NAMES ) ) ) ? explode( 
",", 
$o ) : array();
$excl_links = strToBool( getParam( $_this_->settings, "excludelinks" ) );
$excl_files = ( $o = getParam( $_this_->settings, "excludefiles", null ) ) ? explode( ",", $o ) : array();
foreach ( $excl_files as $key => $value )
if ( in_array( $value, $exclude_files_factory ) )
$excl_files[$key] = @constant( __NAMESPACE__ . '\\' . substr( $value, 1, strlen( $value ) - 2 ) );
$temp_file = tempnam( $wrkdir, WPMYBACKUP_LOGS . '_' );
$verbose = in_array( getParam( $_this_->settings, 'verbose', null ), array( VERBOSE_FULL, VERBOSE_COMPACT ) );
$array = buildFileList( 
$temp_file, 
$_this_->settings, 
$src_dir, 
$excl_dirs, 
$excl_files, 
$excl_ext, 
$excl_links, 
$verbose, 
null );
unlink( $temp_file );
$cache_data['done'] = true;
$cache_data['running'] = false;
$cache_data['files_count'] = $array[0];
$cache_data['filename'] = $array[1];
$cache_data['timestamp'] = time();
file_put_contents( $cache_file, json_encode( $cache_data ) );
add_alert_message( 
sprintf( 
_esc( 'The selected Source Files cache file was built successfully (elapsed time : %d sec)' ), 
time() - $start ), 
null, 
MESSAGE_TYPE_NORMAL );
?>