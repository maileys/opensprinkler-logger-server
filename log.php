<?php

// Saxon Mailey 2015
// saxon at scm dot id dot au

#date_default_timezone_set("Australia/Perth");
date_default_timezone_set("UTC");
# EPOC TIME IS USUALLY STORED AS UTC EPOC TIME HOWEVER THE OPENSPRINKLER
# STORES EPOC TIME RELATIVE TO THE CURRENT TIMEZONE WHICH CAUSES ISSUES
# SETTING THE TIMEZONE TO UTC RESOLVES THIS

require 'db_os.php';

$TABLE="log";
$CONNECT=1;
$IP=$_SERVER['REMOTE_ADDR'];

function checkrecord ($H,$OT) {
	global $DEBUG, $dbcon, $TABLE;
	
	$SQL="SELECT host FROM $TABLE WHERE host=\"$H\" AND ostime=$OT";
	if ( $DEBUG == 1 ) { print " - Checking for existing record for $H @ $OT\n"; }
	if ( $DEBUG == 1 ) { print " - SQL: $SQL\n"; }
	$result = mysql_query("$SQL",$dbcon);
	$num_rows = mysql_num_rows($result);
	return $num_rows;
}

function updatedb ($H,$OT,$DT,$P,$S,$RT) {
	global $DEBUG, $dbcon, $TABLE, $IP;

	$SQL="INSERT INTO $TABLE SET ip=\"$IP\",host=\"$H\",ostime=$OT,time=\"$DT\",pid=$P,sid=$S,runtime=$RT";
	if ( $DEBUG == 1 ) { print "\n - SQL: $SQL\n"; }

	$result = mysql_query("$SQL",$dbcon);
	print "$result\n";
}


if (isset($_GET['host']))    { $HOST    = $_GET['host'];    } else { die("ERROR : host not specified\n"); }
if (isset($_GET['time']))    { $OSTIME  = $_GET['time'];    } else { die("ERROR : time not specified\n"); }
if (isset($_GET['program'])) { $PROGRAM = $_GET['program']; } else { die("ERROR : program not specified\n"); }
if (isset($_GET['station'])) { $STATION = $_GET['station']; } else { die("ERROR : station not specified\n"); }
if (isset($_GET['runtime'])) { $RUNTIME = $_GET['runtime']; } else { die("ERROR : runtime not specified\n"); }
if (isset($_GET['tz']))      { $TZONE   = $_GET['tz'];      } else { $TZONE = 0; }

if (isset($_GET['debug'])) {
	$DEBUG=1;
	print "INPUT VARIABLES\n";
	print " - HOST:    $HOST\n";
	print " - TIME:    $OSTIME\n";
	print " - PROGRAM: $PROGRAM\n";
	print " - STATION: $STATION\n";
	print " - RUNTIME: $RUNTIME\n";
	print " - TZONE:   $TZONE\n";
	print "\n";
	print "WORKING\n";
} else {
	$DEBUG=0;
}

$DATETIME=$OSTIME-($TZONE*60*60);
$DATETIME=date('c', $DATETIME);
if ( $DEBUG == 1 ) {
	print ' - TIME (CONVERTED): ' . date('c', $OSTIME) . "\n";
	print " - TIME (ADJUSTED):  $DATETIME\n";
}

$dbcon = mysql_connect($DBHOST,$DBUSER,$DBPASS);
mysql_select_db($DBNAME, $dbcon);

$RECORDCOUNT = checkrecord($HOST,$OSTIME);
if ($RECORDCOUNT == 0) {
	updatedb($HOST,$OSTIME,$DATETIME,$PROGRAM,$STATION,$RUNTIME);
} else {
	print "WARNING: Record already exists\n";
}

?>
