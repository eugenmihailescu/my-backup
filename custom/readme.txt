Here you can define one|more custom template files (.php) and bind them statically to some custom-defined tabbed menus:
1) first define the menu in the default-target-tabs.php (see $dashboard_tabs array)
	- key: is how your custom .php should be prefixed ; eg. if key={dummy} your files MUST be named {dummy}-tab.php
	- value : is how your tabbed menu button will appear
2) put a .php file named as mentioned above (see key) in this folder

THAT'S ALL FOLKS!

Note: all dynamically defined pages are children of AbstractTargetEditor class (see AbstractTargetEditor.php)
Read also <app-root>/class/lib/editor/readme.txt 