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
 * @file    : 30-build-srcfile-cache.php $
 * 
 * @id      : 30-build-srcfile-cache.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
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