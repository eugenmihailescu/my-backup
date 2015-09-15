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
 * @file    : footer.php $
 * 
 * @id      : footer.php | Mon Sep 14 21:14:57 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

include_once LOCALE_PATH . 'locale.php';
$copyright = explode ( ',', COPYRIGHT );
$sel_lang_code = getSelectedLangCode ();
$lang_url = stripUrlParams ( selfURL (), array (
'lang' 
) );
$lang_url .= (false === strpos ( $lang_url, '?' ) ? '?' : '&') . 'lang=';
$lang_options = '';
foreach ( getAvailableLanguages () as $lang_code => $lang_def ) {
$lang_name = key ( $lang_def );
is_numeric ( $lang_def [$lang_name] ) && $lang_name .= sprintf ( ' (~%s%%)', $lang_def [$lang_name] );
$lang_options .= sprintf ( '<option value="%s" %s>%s</option>', $lang_code, ! empty ( $sel_lang_code ) && ! empty ( $lang_code ) && 0 === strpos ( $sel_lang_code, $lang_code ) ? 'selected="selected"' : '', $lang_name );
}
$section_name = 'Footer';
insertHTMLSection ( $section_name );
?>
<table
style='width: 100%; margin-top: 10px; margin-left: auto; margin-right: auto; text-align: center;'>
<tr>
<td><?php _pesc('Other languages:');?><select
onchange="window.location.href='<?php echo $lang_url;?>'+this.options[this.selectedIndex].value;"><?php echo $lang_options;?></select></td>
</tr>
<tr>
<td>	<?php echo 'v' . APP_VERSION_ID ; ?>
</td>
<tr>
<td><abbr
title='<?php echo !empty($copyright[0])?$copyright[0].' Copyright':'';?>'>&copy;</abbr><?php echo $copyright[0];?>
<a href='<?php echo $copyright[2];?>'><?php echo $copyright[1];?></a></td>
</tr>
</table>
<?php
echo $footer_banner;
$allow_cookie = isset ( $_COOKIE ['cookie_accept'] ) && strToBool ( $_COOKIE ['cookie_accept'] );
if ($allow_cookie && (! isset ( $_COOKIE ['lang'] ) || $sel_lang_code != $_COOKIE ['lang']))
printf ( '<script type="text/javascript">js55f846e1d1da3.setCookie("lang", "%s", 30);</script>', $sel_lang_code );
insertHTMLSection ( $section_name, true );
?>
