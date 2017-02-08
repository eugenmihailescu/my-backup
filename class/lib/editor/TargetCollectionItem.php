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
 * @file    : TargetCollectionItem.php $
 * 
 * @id      : TargetCollectionItem.php | Tue Feb 7 08:55:11 2017 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

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