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
 * @file    : header.php $
 * 
 * @id      : header.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" lang="en-US">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<!--<![endif]-->
<meta http-equiv="x-ua-compatible" content="IE=8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
$styles = array( 'admin', 'admin1' );
foreach ( $styles as $s )
if ( _file_exists( CSS_PATH . WPMYBACKUP_LOGS . '-' . $s . '.css' ) )
printf( 
"<link rel='stylesheet' id='" . WPMYBACKUP_LOGS . "_options_stylesheet-css' href='css/" . WPMYBACKUP_LOGS .
"-$s.css?ver=%s' type='text/css' media='all'/>", 
APP_VERSION_NO );
?>
<style type="text/css">
body {
font-family: monospace;
font-size: 1em;
background-color: #d4f2fe;
}
</style>
<?php
foreach ( array( 'globals', 'admin', 'blockui', 'regex-utils' ) as $js )
if ( _file_exists( JS_PATH . WPMYBACKUP_LOGS . '-' . $js . '.js' ) )
printf( '<script src="js/' . WPMYBACKUP_LOGS . '-' . $js . '.js?ver=%s"></script>', APP_VERSION_NO ) . PHP_EOL;
include_once INC_PATH . 'head.php';
?>
<title><?php echo WPMYBACKUP;?> admin page</title>
</head>