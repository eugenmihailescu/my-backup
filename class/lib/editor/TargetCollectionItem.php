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
 * @file    : TargetCollectionItem.php $
 * 
 * @id      : TargetCollectionItem.php | Wed Sep 16 11:33:37 2015 +0200 | Eugen Mihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyNixWorld;

class TargetCollectionItem {
function __construct($array = null) {
if (is_array ( $array ))
$this->initFromArray ( $array );
}
public $uniq_id;
public $icon;
public $type;
public $enabled;
public $description;
public $targetSettings;
public $title;
public $function_name;
public $folder_style;
public function initFromArray($array) {
$options = array (
'description',
'enabled',
'folder_style',
'function_name',
'icon',
'title',
'type',
'targetSettings' 
);
foreach ( $options as $prop )
isset( $array [ $prop]) && $this->$prop = $array [$prop];
}
}
?>
