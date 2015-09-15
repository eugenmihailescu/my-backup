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
 * @file    : support-functions.php $
 * 
 * @id      : support-functions.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
function echoTicketList($method, $license, $settings = null) {
global $container_shape;
$curl_wrapper = new CurlWrapper ();
$curl_wrapper->initFromArray ( $settings );
$is_error = false;
try {
if (is_array ( $license )) {
$system_id = key ( $license );
} else {
$system_id = null;
}
$info = array (
'system_id' => $system_id,
'action' => 'its',
'tcmd' => 'list' 
);
is_array ( $license ) && $info = array_merge ( $info, $license );
isset ( $_REQUEST ['lang'] ) && $info ['lang'] = $_REQUEST ['lang'];
$result = $curl_wrapper->curlPOST ( LICENSE_REGISTRAR_URL . 'its.php', null, http_build_query ( $info ) );
if (false == $result || null == ($item_count = json_decode ( $result, true )))
$is_error = true;
else
$is_error = null == $item_count && false !== stripos ( $result, 'error' );
} catch ( MyException $e ) {
$is_error = true;
$result = $e->getMessage ();
}
$obj = new SupportCategories ();
$categories = $obj->getCategortList ();
$col_count_html = sprintf ( str_repeat ( '<td style="padding: 5px;">%s</td>', 2 ), _esc ( 'All' ), _esc ( 'Yours' ) );
$row_html = '<tr style="background-color: #00ADEE; color: #FFF; font-weight: bold; text-align: center">';
echo '<table id="ticket_list" class="files ' . $container_shape . '" style="border: 1px solid #00ADEE; padding: 5px; margin: auto;border-spacing:1px;">';
echo $row_html;
printf ( '<td rowspan="2" style="padding: 5px;">%s</td><td colspan="2" style="padding: 5px;">%s</td><td colspan="2" style="padding: 5px;">%s</td>', _esc ( 'Ticket' ) . '<br>' . _esc ( 'category' ), _esc ( 'Opened' ), _esc ( 'Closed' ) );
echo '</tr>';
echo $row_html;
echo $col_count_html . $col_count_html;
echo '</tr>';
if (! $is_error)
foreach ( $item_count as $category_key => $category_status ) {
echo '<tr><td>' . $categories [$category_key] . '</td>';
foreach ( $category_status as $status_key => $status_count ) {
echo '<td style="text-align:center">' . $status_count ['all'] . '</td>';
echo '<td style="text-align:center">' . $status_count ['yours'] . '</td>';
}
echo '</tr>';
}
else
echo "<tr><td colspan='5' style='text-align:center;color:red'>'$result'</td></tr>";
echo '</table>';
}
?>