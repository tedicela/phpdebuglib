# phpdebuglib
PHP library that can be used in any php application. No dependencies of other libraries.


## Description & How to use:
This library can be used in different types of project. It helps developer debugging. 
This library doesn't need addition libraries, It make use of PHP, CSS and raw Javascript. 
So it doesn't need Jquery or other libraries. 

## Display info
Because of the sensible information that it shows this class have some conditions to show these information:
	1. debug 			- 	If we are in the debug mode or not (in use: it can be setted from session or by url or other)
	2. env_condition 	-	This condition can be used for production or staging environment
	3. allowed_ip		-	Debug information will be shown only to specified IP-s, 
								the star ( '*' ) means all IP-s  ex.: $allowed_ip = array(*);
The method setDebugIP($ip_list) - defines the IP when debug info will be available

## Messages
This class handle 4 types of messages: info, warning, error, sql. 
Messages can be setted with: 
``` php
setDebugMessage($errorMessage, $type= 'info', $log = false, $log_file = null)
@errorMessage: 	(string) The message
@type:			(string) The type of the message
@log:			(boolen) Will or will not be logged in file the message
@log_file:		(string) The filename of the alternative log file 
```

## Log messages
This library can be used to log messages in log files: 
	
1.	$log_path		-	It defines the path where the log file will be saved
2.	$log_file		-	The DEFAULT name of the log file
3.	$log_all		-	It defines if all the messages will be logged or not	
To save messages on the log files when you set a message, here is an example:
	$this->setDebugMessage('The message text/html/sql here...', 'info',true);
If you want to save the log in different file from the default:
	$this->setDebugMessage('The message text/html/sql here...', 'info',true, 'new_log_file.log');

## Var_dumping
The class includes a method alternative to var_dump(really: print_r). It make look the dump nice and understandable:
public function debugDump($var, $var_name='', $hide=false)
-if the second argument is set this method put the dump inside a filedset and put a legend with the var_name.
-if the second & third argument are setted (if $hide == true) the dump will be hidden, 
	if you click on the legend it will show the dump 

## Extra
To have the debug info box closed or opened By DEFAULT (so the program will remember if you left the box opened or closed) 
the class use the session $_SESSION['close_debug_info_box'].
The default value is set in the property: $close_debug

## **************** ATTENTION ******************
Properties are private, these can be setted by public functions below....
 