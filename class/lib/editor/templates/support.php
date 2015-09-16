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
 * @file    : support.php $
 * 
 * @id      : support.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><?php printf(_esc('Whenever you have just a question or you need assistence with the usage of this application the starting point is %s.'),getAnchor(_esc('MyBackup Support Center'), APP_ADDONS_SHOP_URI.'get-support'));?>
</td>
</tr>
<tr>
<td>
<?php printf(_esc('Sometimes, in order to help you, we need more technical information about your system. The %s button below provides that kind of information.'),'<b>'._esc('Check PHP setup').'</b>');?>
</td>
</tr>
<tr>
<td style="vertical-align: top;">
<table>
<tr>
<td colspan="3" style='text-align: center'><input type="button"
class="button" value="<?php _pesc('Check PHP setup');?>"
onclick="js55f93aab8f090.php_setup();"><a style='vertical-align: middle' class='help'
onclick=<?php echo echoHelp($help_3 );?>> [?]</a></td>
</tr>
<tr>
<td colspan="3">&nbsp;</td>
</tr>
<tr>
<td colspan="3"><table style="margin: 5px">
<tr>
<td><?php printf(_esc('<b>%s</b> is known to work on'),WPMYBACKUP);?></td>
<td><img src="<?php echo $this->getImgURL ( 'firefox.png');?>"
alt="firefox" title="Firefox"> <img
src="<?php echo $this->getImgURL ( 'chromium.png');?>" alt="chrome"
title="Chome/Chromium"> <img
src="<?php echo $this->getImgURL ( 'ie.png');?>" alt="ie" title="IE"> <img
src="<?php echo $this->getImgURL ( 'opera.png');?>" alt="opera"
title="Opera"></td>
<td><?php echo $iis_apache_php;?></td>
</tr>
</table></td>
</tr>
<tr>
<td><p class="highlight-box hintbox rounded-container"
style="display: inline-block;"><?php
printf ( _esc ( 'An introductive guide about how this sofware works can be found %s.' ), getAnchor ( _esc ( 'here' ), getTabLink ( $TARGET_NAMES [APP_WELCOME] ), '_self' ) );
echo '<br>';
printf ( _esc ( 'For a more comprehensive tuturial visit the %s page.' ), getAnchor ( _esc ( 'Tutorials' ), APP_ADDONS_SHOP_URI . 'tutorials', '_self' ) );
?></p></td>
</tr>
</table>
</td>
</tr>
