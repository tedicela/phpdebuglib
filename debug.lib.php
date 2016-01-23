<?php
/*	Debug
 * 	Author: Tedi Cela
 * 	Date: 28.11.2014
 * 	Version: 1.0
 * 
 * 	Description & How to use:
 * 		This library can be used in different types of project. It helps developer debugging. 
 * 		This library doesn't need addition libraries, It make use of PHP, CSS and raw Javascript. 
 * 		So it doesn't need Jquery or other libraries. 
 * 		
 * 		#Display info
 * 		Because of the sensible information that it shows this class have some conditions to show these information:
 * 			1. debug 			- 	If we are in the debug mode or not (in use: it can be setted from session or by url or other)
 * 			2. env_condition 	-	This condition can be used for production or staging environment
 * 			3. allowed_ip		-	Debug information will be shown only to specified IP-s, 
 * 										the star ( '*' ) means all IP-s  ex.: $allowed_ip = array(*);
 * 		The method setDebugIP($ip_list) - defines the IP when debug info will be available
 * 		
 * 		#Messages
 * 		This class handle 4 types of messages: info, warning, error, sql. 
 * 		Messages can be setted with: 
 * 			setDebugMessage($errorMessage, $type= 'info', $log = false, $log_file = null)
 * 			@errorMessage: 	(string) The message
 * 			@type:			(string) The type of the message
 * 			@log:			(boolen) Will or will not be logged in file the message
 * 			@log_file:		(string) The filename of the alternative log file 
 * 
 * 		#Log messages
 * 		This library can be used to log messages in log files: 
 * 			1.	$log_path		-	It defines the path where the log file will be saved
 * 			2.	$log_file		-	The DEFAULT name of the log file
 * 			3.	$log_all		-	It defines if all the messages will be logged or not	
 * 			To save messages on the log files when you set a message, here is an example:
 * 				$this->setDebugMessage('The message text/html/sql here...', 'info',true);
 * 			If you want to save the log in different file from the default:
 * 				$this->setDebugMessage('The message text/html/sql here...', 'info',true, 'new_log_file.log');
 * 		#Var_dumping
 * 		The class includes a method alternative to var_dump(really: print_r). It make look the dump nice and understandable:
 * 			public function debugDump($var, $var_name='', $hide=false)
 * 			-if the second argument is set this method put the dump inside a filedset and put a legend with the var_name.
 * 			-if the second & third argument are setted (if $hide == true) the dump will be hidden, 
 * 				if you click on the legend it will show the dump 
 * 		#Extra
 * 		To have the debug info box closed or opened By DEFAULT (so the program will remember if you left the box opened or closed) 
 * 		the class use the session $_SESSION['close_debug_info_box'].
 * 		The default value is set in the property: $close_debug
 * 
 * 		**************** ATTENTION ******************
 * 		Properties are private, these can be setted by public functions below....
 * */

class Debug{
	
	private static $instance = null;
	
	//Visualization condition
	private $debug = false; //To distinct normal-mode/debug-mode
	private $env_condition = true; //It can be used for production environment or staging
	private $allowed_ip = array(); //IP list to which the debug info will be displayed
	
	//Messagi
	private $debugMessages = array(); //Html messages
	private $debugRawMessages = array(); //messages in array that can be used in other cases
	
	//Log file
	private $log_path 	= '/tmp/';
	private $log_file 	= 'debug.log';
	private $log_all	= false;
	
	//Grafica
	private $close_debug = true; //Toggle show/hide can be used in combination with the SESSION
	
	//stile dei debug
	private $style = "
<style>
	.debug-output-dump{
		border-radius:6px;
		padding:15px;
		background: #f9c667; /* Old browsers */
		background: -moz-linear-gradient(top, #f9c667 0%, #f79621 100%); /* FF3.6+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f9c667), color-stop(100%,#f79621)); /* Chrome,Safari4+ */
		background: -webkit-linear-gradient(top, #f9c667 0%,#f79621 100%); /* Chrome10+,Safari5.1+ */
		background: -o-linear-gradient(top, #f9c667 0%,#f79621 100%); /* Opera 11.10+ */
		background: -ms-linear-gradient(top, #f9c667 0%,#f79621 100%); /* IE10+ */
		background: linear-gradient(to bottom, #f9c667 0%,#f79621 100%); /* W3C */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f9c667', endColorstr='#f79621',GradientType=0 ); /* IE6-9 */		
	}
	.debug-box{
		border: 2px dashed blueviolet;
		padding:10px;	
		font-family:Arial, Helvetica;
		background-color: #7B7B7B;
	}
	div[class$=\"-message\"]{
		padding: 10px;
		margin-bottom: 20px;
		border: 1px solid transparent;
		border-radius: 4px;
	}
	div[class$=\"-message\"] p:hover{
		cursor:pointer;	 
		color:#CA4128;
	}
	.info-message{
		color:#31708F;
		border-color:#BCE8F1;
		background-color: #D9EDF7;
	}
	.error-message{
		color: #A94442;
		background-color: #F2DEDE;
		border-color: #EBCCD1;
	}
	.warning-message{
		color: #8A6D3B;
		background-color: #FCF8E3;
		border-color: #FAEBCC;
	}
	.sql-message{
		color: #3C763D;
		background-color: #DFF0D8;
		border-color: #D6E9C6;
	}
	.stacktrace{
		list-style:none;
	}
	.stacktrace li{
		font-weight:bold;
	}
	.stacktrace .name{
		font-weight:normal;
	}
	.toggle-text{
		text-decoration:underline;
		cursor:pointer;
	}
	.toggle-label{
		border: 1px solid #ccc;
		margin-right: 10px;
		padding: 5px;
	}
	.float-right{
		float:right;
	}
	.float-left{
		float:left;
	}
	.anchor{
		position:fixed;
		top:20px;
		right:20px;
		text-decoration:none;
	}
	.anchor span{
		border: 1px solid #CCC;
		padding: 10px 0px;
		background-color: #CA4128;
		color: #FFF;
		border-radius: 28px;
	}
</style>";
	
	
	private function __construct(){
		//define the debug mode
		if( ($this->debug) && ($this->env_condition) ){
			$this->debug = true;
		}else{
			$this->debug = false;
		}
		
		//open/close debug info
		if($_SESSION['close_debug_info_box'] == false) $this->close_debug = false; 
		
	}
	
	private static function GI()
	{
		if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
	}
	
	/** Set Debug properties methods starts here... **/
	public static function setMode($mode = false){
		self::GI()->debug = $mode;
	}
	
	public static function setEnvironment($env = true){
		self::GI()->env_condition = $env;
	}
	
	public static function getDebugRawMessages(){
		return self::GI()->debugRawMessages;
	}
	
	public static function setLogPath($path){
		self::GI()->log_path = $path;
	}
	public static function setLogFilename($filename){
		self::GI()->log_file = $filename;
	}
	public static function log_all($log_all = false){
		self::GI()->log_all = $log_all;
	}
	public static function setToggle($toggle = true){
		self::GI()->close_debug = $toggle;
	}
	/** Set Debug properties methods ends here... **/
	
	// This is the core method of the class
	public static function debug(){
		//If we are on the debug mode let's print out debuginfo
		if( (self::GI()->debug == true) && self::GI()->checkDebugIP() ){
			echo "<a href='#debug-wrapper' class='anchor' ><span>Debug</span></a>";
			echo "<div id='debug-wrapper'>";
			echo self::GI()->style;
			echo 	"<div style='padding:20px; color:#FFF; background-color:#CA4128;'>
						Modalita di debug 
						<div class='float-right'>
							<div class='toggle-label float-left'>
							<a href='#debug-box-messages' class='toggle-text' onclick=\"toggle(document.getElementById('debug-box-messages')); toggleText(this); \" >Hide</a> info
							</div>
							<div class='toggle-label float-left'>
							<a href='#debug-global-vars' class='toggle-text' onclick=\"toggle(document.getElementById('debug-global-vars')); toggleText(this); \" >Hide</a> globals
							</div>
						</div>
					</div>";
			echo "<div id='debug-global-vars'>";
					self::GI()->debugDump($_SESSION, 'SESSION', true);
					self::GI()->debugDump($_POST, 'POST', true);
					self::GI()->debugDump($_GET, 'GET', true);
					self::GI()->debugDump($_SERVER, 'SERVER', true);
			echo "</div>";
			echo self::GI()->getDebugMessages();
			echo "</div>";
		}else{ //else do nothin
			return false;
		}
	}
	
	public static function setDebugIP($ip){
		self::GI()->allowed_ip = $ip;
	}
	
	public static function debugDump($var, $var_name='', $hide=false){
			if(self::GI()->debug == true){
				$style = "style='display:block;'";
				if($hide == true) $style = "style='display:none;'";
				$print_r = "<pre {$style}>".print_r($var, true)."</pre>";
				if(trim($var_name) != ''){
				$out = "<div class='debug-output-dump'>
							<fieldset>
							<legend onclick=\"toggle(this.parentNode.getElementsByTagName('pre')[0]);\" style='cursor:pointer;' >{$var_name}</legend>
								".$print_r."
							</fieldset>
						</div>";
				}else{ 
					$out = "<div class='debug-output-dump'>
								".$print_r."
							</div>";
				}
				echo $out;
			}else{
				return false;
			}
	}
	
	public static function setDebugMessage($errorMessage, $type= 'info', $log = false, $log_file = null){
		
		//defining Message types:
		$types = array('info', 'error', 'warning','sql');
		if( !in_array($type, $types) ){ 
			self::GI()->setDebugMessage("Second argument $<i>type</i> not valid: $type", 'error');
		}
		if(strtolower($type) == 'sql') $errorMessage = htmlentities($errorMessage);
		
		//Let's find the stacktrace:
		$trace=debug_backtrace();
		
		if(!empty($trace[1]) ){
			$caller=$trace[1];
		}else{ 
			$caller = $trace[0];
			$caller['class'] = '';
			$caller['function'] = '';
		}
		
		$message = array(
					'message'	=> $errorMessage,
					'type'		=> $type,
					'caller'	=> $caller
					);
		
		//Write in the log-file if log == true
		if($log == true) {
			if($log_file != null)
				self::GI()->logDebugMessage($message, $log_file);
			else
			self::GI()->logDebugMessage($message);
		}
		
		//Add it as Raw message
		array_push(self::GI()->debugRawMessages, $message);

		//Create it in HTML message:
		//create the stacktrace:
		$stacktrace = "<ul class = 'stacktrace' style='display:none'>";
		if(strtolower($type) == 'sql') $stacktrace .= "<li class='query'><textarea readonly onfocus='this.select();' onclick='this.select();' cols=50>".self::GI()->raw_sql($errorMessage)."</textarea></li>";
		if (isset($caller['class'])) $stacktrace .= "<li class='class'>Class: <span class='name'>{$caller['class']}</span</li>";
		$stacktrace .= "<li class = 'function'>Function: <span class='name'>{$caller['function']}</span></li>";
		$stacktrace .= "<li class = 'file'>File: <span class='name'>{$caller['file']}</span></li>";
		$stacktrace .= "<li class = 'line'>Line: <span class='name'>{$caller['line']}</span></li>";
		$stacktrace .= "</ul>";
		
		//create the message:
		$message = "<div class='{$type}-message' >
						<p class='message' onclick=\"toggle(this.parentNode.getElementsByTagName('ul')[0]);\">{$errorMessage}</p>
						{$stacktrace}
					</div>";
		self::GI()->debugMessages[] = $message;
	}
	
	private function getDebugMessages(){
		$style = "style='display:block;'";
		if($this->close_debug) $style = "style='display:none;'";
		$debug 	=	"<div id='debug-box-messages' class='debug-box' {$style} >";
		$debug .= 	"<script type='text/javascript'>
							function toggle(elem){
								if(elem.style.display == 'none'){
									elem.style.display='block';
								}else{
									elem.style.display='none';
								}
							}
							function toggleText(elem){
								if(elem.innerHTML.toLowerCase() == 'hide')
									elem.innerHTML = 'Show';
								else
									elem.innerHTML = 'Hide';
							}
					</script>";
		foreach($this->debugMessages as $message){
			$debug .= $message;
		}
		$debug .= "</div>";
		
		return $debug;
	}
	
	//Log messages
	private function logDebugMessage($log_message, $log_file = null){
		
		if($log_file == null) $log_file = $this->log_file;
		
		if(!empty($log_message['caller']['class']) ) $log_message['caller']['class'] = ' Class '.$log_message['caller']['class'].'->';
		if(!empty($log_message['caller']['function'])) $log_message['caller']['function'] = $log_message['caller']['function']." on ";
 		$log_line = "\n DEBUG ".ucfirst($log_message['type']).": ".$log_message['message']." on".$log_message['caller']['class']." ".$log_message['caller']['function']."file: ".$log_message['caller']['file'].' on line: '.$log_message['caller']['line'];
		
		$this->make_path($this->log_path);
		
		$log_file = rtrim($this->log_path,'/').'/'.$log_file;
		
		$log_stream = fopen($log_file, 'a+');
		fwrite($log_stream, $log_line);
		fclose($log_stream);
	}
	
	/*Create  Directory Tree if Not Exists*/
	private function make_path($pathname){
		if(!is_dir($pathname) )
		return mkdir($pathname,0777,true);	
	}
	
	//To make the query ready for execution
	private function raw_sql($sql){
		$raw_sql = preg_replace('/\r\n|\r|\n\r|\n/m', ' ', $sql);
		return $raw_sql;
	}
	
	//Check if the client IP is allowed
	private function checkDebugIP(){
		if(strtolower($this->allowed_ip[0]) == '*' ) return true;
		else if( in_array($_SERVER['REMOTE_ADDR'], $this->allowed_ip) ) return true;
		else return false;
	}
	
	private function __clone()
    {
    	
    }
	
	private function __wakeup()
    {
    	
    }

}
?>	

