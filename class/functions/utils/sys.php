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
 * @version : 1.0-2 $
 * @commit  : f8add2d67e5ecacdcf020e1de6236dda3573a7a6 $
 * @author  : eugenmihailescu <eugenmihailescux@gmail.com> $
 * @date    : Tue Dec 13 06:40:49 2016 +0100 $
 * @file    : sys.php $
 * 
 * @id      : sys.php | Tue Dec 13 06:40:49 2016 +0100 | eugenmihailescu <eugenmihailescux@gmail.com> $
*/

namespace MyBackup;

function _usleep( $micro_seconds ) {
if ( function_exists( '\\usleep' ) && ! ini_get( 'safe_mode' ) )
usleep( $micro_seconds );
else
_sleep( $micro_seconds / 1e6 );
}
function _sleep( $seconds ) {
if ( function_exists( '\\sleep' ) && ! ( ini_get( 'safe_mode' ) || function_is_restricted( 'sleep' ) ) )
sleep( $seconds );
}
function isWin() {
return preg_match( '/^win/i', PHP_OS );
}
function isUnix() {
return ! isWin();
}
function execNoWait( $cmd ) {
if ( isWin() ) {
$res = proc_open( "start /B " . $cmd, "r" );
$pid = proc_get_status( $res );
pclose( $res );
return $pid['pid'];
} else {
exec( $cmd . " > /dev/null 2>&1 & echo $1", $output );
$output = explode( ' ', $output );
return $output[1];
}
}
function wmiWBemLocatorQuery( $query ) {
if ( class_exists( '\\COM' ) ) {
try {
$WbemLocator = new \COM( "WbemScripting.SWbemLocator" );
$WbemServices = $WbemLocator->ConnectServer( '127.0.0.1', 'root\CIMV2' );
$WbemServices->Security_->ImpersonationLevel = 3;
return $WbemServices->ExecQuery( $query );
} catch ( \com_exception $e ) {
echo getSpan( $e->getMessage(), 'red' );
}
} elseif ( ! extension_loaded( 'com_dotnet' ) )
trigger_error( 'It seems that the COM is not enabled in your php.ini', E_USER_WARNING );
else {
$err = error_get_last();
trigger_error( $err['message'], E_USER_WARNING );
}
return false;
}
function get_system_load( $interval = 300 ) {
if ( ! isWin() ) {
$sys_load = sys_getloadavg();
return $sys_load[$interval < 60 ? 0 : ( $interval < 300 ? 1 : 2 )] / getCpuCount();
} else 	// WINDOWS
{
$load = 0;
$wmi_found = false;
if ( $wmi_query = wmiWBemLocatorQuery( "SELECT LoadPercentage FROM Win32_Processor" ) ) {
if ( false !== $wmi_query ) {
foreach ( $wmi_query as $c ) {
$load += $c->LoadPercentage;
$wmi_found = false;
}
$load /= count( $wmi_query );
}
}
if ( ! $wmi_found ) {
$cmd = 'typeperf  -sc 1  "\Processor(_Total)\% Processor Time"';
exec( $cmd, $lines, $retval );
if ( $retval == 0 ) {
$values = str_getcsv( $lines[2] );
$load = floatval( $values[1] );
} else {
return 0;
}
}
$load /= 100; 
}
return $load;
}
function getCpuInfo() {
$result = array();
try {
if ( isWin() ) {
$wmi_found = false;
if ( $wmi_query = wmiWBemLocatorQuery( 
"SELECT Name,Manufacturer,Family,Stepping,CurrentClockSpeed,MaxClockSpeed FROM Win32_Processor" ) ) {
$cpuid = 0;
foreach ( $wmi_query as $r ) {
$result[$cpuid] = array();
$result[$cpuid]['vendor_id'] = $r->Manufacturer;
$result[$cpuid]['model name'] = $r->Name . ' @ ' . $r->MaxClockSpeed . 'MHz';
$result[$cpuid]['stepping'] = $r->Stepping;
$result[$cpuid]['cpu_MHz'] = $r->CurrentClockSpeed;
$result[$cpuid]['stepping'] = $r->Stepping;
$result[$cpuid]['core_id'] = $cpuid;
$cpuid++;
$wmi_found = true;
}
}
if ( ! $wmi_found ) {
$cpu_count = $_SERVER['NUMBER_OF_PROCESSORS'];
for ( $cpuid = 0; $cpuid < $cpu_count; $cpuid++ ) {
if ( preg_match( '/Family (\d+)/', $_SERVER['PROCESSOR_IDENTIFIER'], $matches ) )
$result[$cpuid]['cpu_family'] = $matches[1];
if ( preg_match( '/Model (\d+)/', $_SERVER['PROCESSOR_IDENTIFIER'], $matches ) )
$result[$cpuid]['model'] = $matches[1];
if ( preg_match( '/Stepping (\d+)/', $_SERVER['PROCESSOR_IDENTIFIER'], $matches ) )
$result[$cpuid]['stepping'] = $matches[1];
$result[$cpuid]['model name'] = $_SERVER['PROCESSOR_ARCHITECTURE'] . ' level ' .
$_SERVER['PROCESSOR_LEVEL'] . ' rev ' . $_SERVER['PROCESSOR_REVISION'];
$result[$cpuid]['vendor_id'] = 1 === preg_match( '/intel/i', $_SERVER['PROCESSOR_IDENTIFIER'] ) ? 'GenuineIntel' : 'unknown';
$result[$cpuid]['cpu_MHz'] = '0';
}
}
} else 		// LINUX
{
$processor = null;
$proc_dir = '/proc/';
$data = _dir_in_allowed_path( $proc_dir ) ? @file( $proc_dir . 'cpuinfo' ) : false;
if ( is_array( $data ) )
foreach ( $data as $d ) {
if ( 0 == strlen( trim( $d ) ) )
continue;
$d = preg_split( '/:/', $d );
$key = trim( $d[0] );
$value = trim( $d[1] );
if ( 'processor' == $key ) {
$processor = $value;
$result[$processor] = array();
} elseif ( null != $processor )
$result[$processor][$key] = $value;
}
}
} catch ( MyException $e ) {
echo $e->getMessage();
}
return $result;
}
function getSystemMemoryInfo( $output_key = '' ) {
$keys = array( 'MemTotal', 'MemFree', 'MemAvailable', 'SwapTotal', 'SwapFree' );
$result = array();
try {
if ( ! isWin() ) {
$proc_dir = '/proc/';
$data = _dir_in_allowed_path( $proc_dir ) ? @file( $proc_dir . 'meminfo' ) : false;
if ( is_array( $data ) )
foreach ( $data as $d ) {
if ( 0 == strlen( trim( $d ) ) )
continue;
$d = preg_split( '/:/', $d );
$key = trim( $d[0] );
if ( ! in_array( $key, $keys ) )
continue;
$value = 1000 * floatval( trim( str_replace( ' kB', '', $d[1] ) ) );
$result[$key] = $value;
}
} else 		// WINDOWS
{
$wmi_found = false;
if ( $wmi_query = wmiWBemLocatorQuery( 
"SELECT FreePhysicalMemory,FreeVirtualMemory,TotalSwapSpaceSize,TotalVirtualMemorySize,TotalVisibleMemorySize FROM Win32_OperatingSystem" ) ) {
foreach ( $wmi_query as $r ) {
$result['MemFree'] = $r->FreePhysicalMemory * 1024;
$result['MemAvailable'] = $r->FreeVirtualMemory * 1024;
$result['SwapFree'] = $r->TotalSwapSpaceSize * 1024;
$result['SwapTotal'] = $r->TotalVirtualMemorySize * 1024;
$result['MemTotal'] = $r->TotalVisibleMemorySize * 1024;
$wmi_found = true;
}
}
}
} catch ( MyException $e ) {
echo $e->getMessage();
}
return empty( $output_key ) || ! isset( $result[$output_key] ) ? $result : $result[$output_key];
}
function getPIDUsage( $pid = null ) {
if ( $pid == null )
$pid = getmypid();
if ( isWin() ) {
return 0; 
} else {
exec( "ps -p $pid -o %C", $output );
return floatval( $output[1] );
}
}
function getCpuCount() {
$count = 0;
$proc_dir = '/proc/';
$proc_stat = $proc_dir . 'stat';
if ( isWin() ) {
if ( $wmi_query = wmiWBemLocatorQuery( "SELECT NumberOfProcessors FROM Win32_ComputerSystem" ) )
$count = count( $wmi_query );
} 	
elseif ( ! strToBool( ini_get( 'safe_mode' ) ) &&
( _dir_in_allowed_path( $proc_dir ) ? @_is_file( $proc_stat ) : false ) ) {
$cpuinfo = @file_get_contents( $proc_stat );
$count = preg_match_all( '/^cpu\d/m', $cpuinfo );
}
return 0 == $count ? 1 : $count; 
}
?>