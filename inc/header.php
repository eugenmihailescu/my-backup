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
 * @version : 0.2.2 $
 * @commit  : 23a9968c44669fbb2b60bddf4a472d16c006c33c $
 * @author  : Eugen Mihailescu <eugenmihailescux@gmail.com> $
 * @date    : Wed Sep 16 11:33:37 2015 +0200 $
 * @file    : header.php $
 * 
 * @id      : header.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
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
$styles = array (
'admin',
'admin1' 
);
foreach ( $styles as $s )
if (file_exists ( CSS_PATH . WPMYBACKUP_LOGS . '-' . $s . '.css' ))
printf ( "<link rel='stylesheet' id='" . WPMYBACKUP_LOGS . "_options_stylesheet-css' href='css/" . WPMYBACKUP_LOGS . "-$s.css?ver=%s' type='text/css' media='all'/>", APP_VERSION_NO );
?>
<style type="text/css">
body {
font-family: monospace;
font-size: 1em;
background-color: #d4f2fe;
}
</style>
<?php
foreach ( array (
'globals',
'admin',
'blockui' 
) as $js )
if (file_exists ( JS_PATH . WPMYBACKUP_LOGS . '-' . $js . '.js' ))
printf ( '<script src="js/' . WPMYBACKUP_LOGS . '-' . $js . '.js?ver=%s"></script>', APP_VERSION_NO ) . PHP_EOL;
?>
<script type="text/javascript">Date.now = Date.now || function() { return +new Date; }; window.page_start_loading=Date.now();</script>
<title><?php echo WPMYBACKUP;?> admin page</title>
</head>
