<?php

// Saxon Mailey 2015
// saxon at scm dot id dot au

date_default_timezone_set("Australia/Perth");

if (isset($_GET['host']))    { $HOST    = $_GET['host'];    } else { die("ERROR : host not specified\n"); }

$TITLEPRE = "$HOST" . ' - Irrigation - Watering Times';
$WIDTH    = 700;
$HEIGHT   = 250;
$COLOR    = '#00EE00';
$SEQ      = 0;
$HTMLOUT  = '';

require 'db_os.php';
$dbcon = mysql_connect($DBHOST,$DBUSER,$DBPASS);
mysql_select_db($DBNAME, $dbcon);


print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
print '<HTML xmlns="http://www.w3.org/1999/xhtml">' . "\n";
print "\t" . '<head>' . "\n";
print "\t\t" . '<link rel="stylesheet" href="default.css" type="text/css" />' . "\n";
print "\t\t" . '<title>' . $TITLEPRE . '</title>' . "\n";
print "\t\t" . '<script type="text/javascript" src="https://www.google.com/jsapi"></script>' . "\n";
print "\t\t" . '<script type="text/javascript">' . "\n";
print "\t\t\t" . 'google.load("visualization", "1", {packages:["corechart"]});' . "\n";
print "\t\t" . '</script>' . "\n";


function drawchart ($TITLE, $UNIT, $SQL)
{
	global $HTMLOUT, $SEQ, $dbcon, $FEED, $CHANNEL, $WIDTH, $HEIGHT, $COLOR;

	$SEQ=$SEQ+1;
	$result = mysql_query($SQL,$dbcon);
	$rows=mysql_num_rows($result);
	if ($rows > 0) {
		print "\t\t" . '<script type="text/javascript">' . "\n";
		print "\t\t\t" . 'function drawVisualization() {' . "\n";
		print "\t\t\t\t" . 'var data' . $SEQ . ' = google.visualization.arrayToDataTable([' . "\n";
		print "\t\t\t\t\t" . "['Day', '" . $UNIT . "']," . "\n";
	
		$i=0;
		while ($i < $rows) {
			if ($i > 0) { print ",\n"; }
			print "\t\t\t\t\t['" . mysql_result($result,$i,"date") . "'," . mysql_result($result,$i,"runtime") . "]";
			$i++;
		}
	
		print "\n\t\t\t\t" . "]);" . "\n\n";
		print "\t\t\t\t" . "new google.visualization.LineChart(document.getElementById('visualization" . $SEQ . "'))." . "\n";
		print "\t\t\t\t" . 'draw(data' . $SEQ . ', {title:"' . $TITLE . '", width:' . $WIDTH . ', height:' . $HEIGHT . ', vAxis: {minValue: 0}, legend: "none", series: { 0: { color: "' . $COLOR . '" }}});' . "\n";
		print "\t\t\t" . '}' . "\n";
		print "\t\t\t" . 'google.setOnLoadCallback(drawVisualization);' . "\n";
		print "\t\t" . '</script>' . "\n";
		$HTMLOUT = $HTMLOUT . '<div id="visualization' . $SEQ . '" style="width: ' . $WIDTH . 'px; height: ' . $HEIGHT . 'px;"></div>' . "\n";
		//$HTMLOUT = $HTMLOUT . $SQL . "\n";
	} else {
                print "\n<P>\n" . '<CENTER><FONT SIZE="-1">' . $TITLE . ' - NO DATA FOUND</FONT></CENTER>' . "\n<P>\n";
	}
	print "<!-- $SQL -->\n";
}



$UNIT     = 'minutes';
$TITLE    = "$TITLEPRE - 2 Weeks ($UNIT)";
$SQL      = 'SELECT DATE_FORMAT(time, "%W") as date, ROUND(SUM(runtime)/60) as runtime from v_time_diff';
$SQL=$SQL . ' WHERE host="' . $HOST . '" AND time > DATE_SUB(NOW(), INTERVAL 2 WEEK)';
$SQL=$SQL . ' GROUP BY DATE(time)';
$SQL=$SQL . ' ORDER BY time';
drawchart($TITLE, $UNIT, $SQL);

$UNIT     = 'minutes';
$TITLE    = "$TITLEPRE - 2 Months ($UNIT)";
$SQL      = 'SELECT DATE_FORMAT(time, "%b %d") as date, ROUND(SUM(runtime)/60) as runtime from v_time_diff';
$SQL=$SQL . ' WHERE host="' . $HOST . '" AND time > DATE_SUB(NOW(), INTERVAL 2 MONTH)';
$SQL=$SQL . ' GROUP BY DATE(time)';
$SQL=$SQL . ' ORDER BY time';
drawchart($TITLE, $UNIT, $SQL);

$UNIT     = 'minutes';
$TITLE    = "$TITLEPRE - 1 Year ($UNIT)";
$SQL      = 'SELECT DATE_FORMAT(time, "%Y %b") as date, ROUND(SUM(runtime)/60) as runtime from v_time_diff';
$SQL=$SQL . ' WHERE host="' . $HOST . '" AND time > DATE_SUB(NOW(), INTERVAL 1 YEAR)';
$SQL=$SQL . ' GROUP BY DATE(time)';
$SQL=$SQL . ' ORDER BY time';
drawchart($TITLE, $UNIT, $SQL);

$UNIT     = 'minutes';
$TITLE    = "$TITLEPRE - History ($UNIT)";
$SQL      = 'SELECT DATE_FORMAT(time, "%Y %b") as date, ROUND(SUM(runtime)/60) as runtime from v_time_diff';
$SQL=$SQL . ' WHERE host="' . $HOST . '"';
$SQL=$SQL . ' GROUP BY DATE(time)';
$SQL=$SQL . ' ORDER BY time';
drawchart($TITLE, $UNIT, $SQL);


print "\t" . '</head>' . "\n";
print "\t" . '<BODY>' . "\n";

print "\n" . $HTMLOUT . "\n";

print "\n\t" . '</BODY>' . "\n";
print '</HTML>' . "\n";
?>
