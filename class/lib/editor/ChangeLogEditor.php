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
 * @version : 0.2.3-30 $
 * @commit  : 11b68819d76b3ad1fed1c955cefe675ac23d8def $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Fri Mar 18 17:18:30 2016 +0100 $
 * @file    : ChangeLogEditor.php $
 * 
 * @id      : ChangeLogEditor.php | Fri Mar 18 17:18:30 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;
class ChangeLogEditor extends AbstractTargetEditor {
protected function initTarget() {
parent::initTarget ();
$this->hasCustomFrame = true;
}
protected function getEditorTemplate() {
$yayui = new YayuiCompressor ();
$options = array (
PRESERVE_STRINGS => false, 
SINGLE_COMMENT => true,
BLOCK_COMMENT => true,
LINE_SEPARATOR => true,
WHITESPACE => true,
MINIFY => true 
);
echo '<style>';
echo $yayui->streamCompress ( file_get_contents ( $this->getTemplatePath ( 'changelog.css' ) ), $options, 'CSS', false );
echo '</style>';
echo $yayui->htmlCompress ( file_get_contents ( $this->getTemplatePath ( 'changelog.html', ROOT_PATH ) ), $options );
}
}
?>