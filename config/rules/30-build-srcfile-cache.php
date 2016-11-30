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
 * @file    : 30-build-srcfile-cache.php $
 * 
 * @id      : 30-build-srcfile-cache.php | Tue Nov 29 16:33:58 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

global $java_scripts;
$cache_file = LOG_PREFIX . '-srcfiles.cache';
if ( is_file( $cache_file ) && $cache_data = json_decode( file_get_contents( $cache_file ), true ) ) {
if ( ! ( $cache_data['done'] || $cache_data['running'] ) && time() - $cache_data['timestamp'] > 300 ) {
$action = 'srcfiles_recalc_cache';
$nonce = wp_create_nonce_wrapper( 'srcfiles_recalc_cache' );
$java_scripts[] = sprintf( 
'parent.asyncGetContent(parent.ajaxurl, "action=%s&nonce=%s", parent.dummy);', 
$action, 
$nonce );
}
}
?>