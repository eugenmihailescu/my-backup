// Generated automatically at 2015-12-28 05:58 PM

Here are written the .log files.

Their aim are:
 - to answer the simple question like `why my file hasn't been uploaded to Dropbox?`  
 - to provide a trace for later debugging (yeah, sometimes a nasty bug shits right on your precious file; sorry mate, I didn't that by purpose :-(
 - to provide the raw resource for what is actually displayed on screen (write the log first, echo its content later) 
 
The known log files and their meaning are as the following:
			
-files-md5.log" ); // keeps the track of files MD5 hash value
-backup-filter.log" ); // keeps the track of the last MD5 hash values for each backup mode
-curl-debug.log" ); // all CURL operations
-statistics-debug.log" ); // all SQL queries/JavaScripts related to statistics
-trace-debug.log" ); // all PHP errors/exceptions (even those thrown by the app)
-errors.log" ); // PHP unexpected errors
-jobs.log" ); // just an entry to count the job started/done
-output.log" ); // everything that is redirected to the output is written here first
-nonces.log" ); // application internal security nonces file
-messages.log" ); // application internal notification/message queue
-trace-action.log" ); // a log of what commands the application received from user/browser
-smtp-debug.log" ); // all PHP Mail debug log
-curl-cookies.log" ); // used by Curl to store the eventually cookies
-curl-cookies.jar" ); // used by Curl to archive the cookies
-jobs.lock" ); // a lock file that should exist only during the execution of backup/benchmark job
-progress.log" ); // the UI progress lock file
-stats.log" ); // SQLite database where all statistical data are stored (see Statistics/Job history)
-options.json" ); // the application settings
-signals.log" ); // the signals which a job can receive while running async (eg: abort job!)
-net-usage.log" ); // used by Linux/Win System Usage charts to measure the current net usage
			
NOTE: please DO NOT DELETE these files. Without them you have no backup log history, no statistics, no settings. It's like starting fresh for the first time.
Anyway if that is what you REALLY want then...you are the boss, boss ;-)
		