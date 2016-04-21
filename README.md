# MyBackup
An OS independent backup application

MyBackup is a web application that enables blog authors and system administrators to backup their blog and/or system files with ease. It allows you to create a full, differential or incremental backups of both files and your MySQL databases. Furthermore it secures the backup by uploading it to the local disk, Ftp(s), Scp, SFtp, Dropbox, Google Drive, Webdav or sent via SMTP within a single/multiple e-mail messages as attachments.

The backup may be stored uncompressed (as a TAR archive) or compressed within a TAR/Zip archives using the GZip/BZip/LZF respectively Zip compression. Nonetheless the archive may be encrypted on the fly with an AES (Rijndael) cipher using a 128/192/256 bit key such that its content is protected from the curious eyes (it will take billions years to crack a 256-bit key).

MyBackup allows you to restore with ease any backup created by it thanks to an integrated Restore Wizard interface.

In order to help you understand how it works and/or diagnose a particular issue it includes enhanced debugging functionalities. The backup and restore jobs, the HTTP communication, the PHP and Ajax calls as well as well as the SMTP and SQL statements, all are logged into separated detailed log files.
Major features supported* by MyBackup:

* Support for creating full (complete), incremental and differential backups
* Support for splitting a large backup into multiple archive volumes
* Allows you to select the files and folders to include/exclude, how to store and where to store the backups
* Allows a complete backup of the system by giving you access to the whole file system
* Comes with support for backing up any remote MySQL database
* Additionally, allows MySQL backups via the local mysqldump toolchain including custom options support
* Allows the usage of External OS compression toolchain (additionally to its default built-in compression toolchain)
* Offers Zip archive support for maximum portability and LZF compression for maximim speed
* Encrypts/decrypts the backup archives using the AES (Rijndael) cipher with a 128/192/256 bit key
* Allows backup execution from command line via a complete CLI interface
* Support for restoring a full, increment or differential backup set created by itself
* Allows definition of multiple backup and restore jobs via an user-friendly Wizard
* Allows backup schedule at the OS level where the backup job is run via the CLI interface

Other features you will love:

* Allows saving the CPU and network bandwidth during the backup execution by limiting (throttling) the usage of these resources
* Comes with an enhanced backup history integrated with statistics and charting
* Allows tweaking the networking settings (like proxy, SSL, throttling, network interface, timeout, etc)
* Comes with file explorer support to allow you access any file from the local/remote storage (like local disk, Dropbox, Google, FTP, SSH, WebDav, etc)
* The file explorer allows direct operations on the local and cloud storages such as direct downloads, delete, rename or directory creation
* Keeps the track of what is doing in separate debug log files: backup/restore jobs, HTTP communication, PHP errors/back-traces and Ajax calls, SMTP communication, SQL statements, etc.
* Automatic log archiving and rotation
