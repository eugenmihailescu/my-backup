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
 * @file    : support-contact.php $
 * 
 * @id      : support-contact.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;
?>
<tr>
<td><label for="email"><?php _pesc('Author');?></label></td>
<td>
<?php
$author_email = getPluginAuthorEmail ();
$author_name = getPluginAuthorName ();
if (! empty ( $author_email ) || ! empty ( $author_name ))
echo sprintf ( '<a id="email" href="mailto:%s">%s</a>', $author_email, $author_name );
?>
</td>
</tr>
<tr>
<td><label for="web_link"><?php _pesc('URL');?></label></td>
<td><a id="web_link" href='http://mynixworld.info/wpmybackup'>http://mynixworld.info/wpmybackup</a></td>
</tr>
<tr>
<?php $lnk_str=_esc('You cand find me on ');?>
<td><label for="contact"><?php _pesc('Contact');?></label></td>
<td id="contact"><a title='<?php echo $lnk_str;?>Facebook'
target="_blank" href='http://facebook.com/eugenmihailescu'><img
alt="facebook"
src=<?php
echo '"' . $this->getImgURL ( 'facebook.png' ) . '"';
?>></a> <a title='<?php echo $lnk_str;?>Twitter' target="_blank"
href='http://twitter.com/eugenmihailescu'><img alt="twitter"
src=<?php
echo '"' . $this->getImgURL ( 'twitter.png' ) . '"';
?>></a> <a title='<?php echo $lnk_str;?>Google+' target="_blank"
href='http://plus.google.com/+EugenMihailescu?rel=author'><img
alt="googleplus"
src=<?php
echo '"' . $this->getImgURL ( 'googleplus.png' ) . '"';
?>></a> <a title='<?php echo $lnk_str;?>LinkedIn' target="_blank"
href='http://www.linkedin.com/in/eugenmihailescu'><img alt="linkedin"
src=<?php
echo '"' . $this->getImgURL ( 'linkedin.png' ) . '"';
?>></a></td>
</tr>
