<?php

// Saxon Mailey 2015
// saxon at scm dot id dot au

date_default_timezone_set("Australia/Perth");

require 'db_os.php';

# DEFAULT SQL
$SQLDATE='DATE(datetime) as date';
$SQLGROUP='date';
$DEFAULT_DURATION=28;
#$SQL='SELECT DATE(datetime) as date, ROUND(AVG(value),2) as value FROM data WHERE feed=' . $FEED . ' AND channel="' . $CHANNEL . '" GROUP BY date';

if (isset($_GET['debug'])) {
        $DEBUG = $_GET['debug'];
}

$TYPE="CHART";

if (isset($_GET['title']))    { $TITLE   = $_GET['title']; }     else { $TITLE=""; }
if (isset($_GET['width']))    { $WIDTH = $_GET['width']; }       else { if (isset($_GET['w']))  { $WIDTH = $_GET['w']; }     else { $WIDTH=800; } }
if (isset($_GET['height']))   { $HEIGHT = $_GET['height']; }     else { if (isset($_GET['h']))  { $HEIGHT = $_GET['h']; }    else { $HEIGHT=400; } }
if (isset($_GET['host']))  { $HOST    = $_GET['host'];    } else { die("ERROR : host not specified\n"); }
if (isset($_GET['start'])) { $DATESTART = $_GET['start']; } else { if (isset($_GET['s']))  { $DATESTART = $_GET['s']; } else { $DATESTART=''; } }
if (isset($_GET['end']))   { $DATEEND = $_GET['end'];     } else { if (isset($_GET['e']))  { $DATEEND = $_GET['e']; }   else { $DATEEND=''; } }

$dbcon = mysql_connect($DBHOST,$DBUSER,$DBPASS);
mysql_select_db($DBNAME, $dbcon);

$SQLWHERE = 'host="' . $HOST . '"';

if ("x$DATESTART" <> "x") {
        $SQLWHERE=$SQLWHERE . ' AND date(datetime) >= ' . "'$DATESTART'";
}
if ("x$DATEEND" <> "x") {
        $SQLWHERE=$SQLWHERE . ' AND date(datetime) <= ' . "'$DATEEND'";
}

$SQL='SELECT date, settime, runtime FROM v_chart_time_comparison WHERE ' . $SQLWHERE;
$result = mysql_query($SQL,$dbcon);
$rows=mysql_num_rows($result);
if ($rows > 0) {
                $DESCRIPTION = mysql_result($result,0,"description");
}

$result = mysql_query($SQL,$dbcon);
$rows=mysql_num_rows($result);
if ($rows > 0) {


        print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
        print '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
        if ("$TYPE" == "CHART") {
                print "\t" . '<head>' . "\n";
                print "\t\t" . '<script type="text/javascript" src="https://www.google.com/jsapi"></script>' . "\n";
                print "\t\t" . '<script type="text/javascript">' . "\n";
                print "\t\t\t" . 'google.load("visualization", "1", {packages:["corechart"]});' . "\n";
                print "\t\t" . '</script>' . "\n";
                print "\t\t" . '<script type="text/javascript">' . "\n";
                print "\t\t\t" . 'function drawVisualization() {' . "\n";
                print "\t\t\t\t" . 'var data = google.visualization.arrayToDataTable([' . "\n";
                print "\t\t\t\t\t" . "['Day','Run Time','Set Time']," . "\n";

                $i=0;
                while ($i < $rows) {
                        if ($i > 0) { print ",\n"; }
                        print "\t\t\t\t\t['" . mysql_result($result,$i,"date") . "'," . mysql_result($result,$i,"runtime") . "," . mysql_result($result,$i,"settime") . "]";
                        $i++;
                }

                print "\n\t\t\t\t" . "]);" . "\n\n";
                #print "\t\t\t\t" . "new google.visualization.ColumnChart(document.getElementById('visualization'))." . "\n";
                print "\t\t\t\t" . "new google.visualization.LineChart(document.getElementById('visualization'))." . "\n";
                #print "\t\t\t\t" . 'draw(data, {title:"' . $TITLE . '", width:' . $WIDTH . ', height:' . $HEIGHT . ', hAxis: {title: "Year"}});' . "\n";
                print "\t\t\t\t" . 'draw(data, {title:"' . $TITLE . '", width:' . $WIDTH . ', height:' . $HEIGHT . ', });' . "\n";
                print "\t\t\t" . '}' . "\n";
                print "\t\t\t" . 'google.setOnLoadCallback(drawVisualization);' . "\n";
                print "\t\t\t" . '</script>' . "\n";
                print "\t\t" . '</head>' . "\n";
                print "\t" . '<body>' . "\n";
                #print "\t" . $DESCRIPTION . "<P>\n";
                print "\t\t" . '<div id="visualization" style="width: ' . $WIDTH . 'px; height: ' . $HEIGHT . 'px;"></div>' . "\n";
        } else {
                print "\t" . '<body>' . "\n";
                print "\t\t" . '<TABLE>'  . "\n";
                $i=0;
                while ($i < $rows) {
                        print "\t\t\t" . '<TR><TD>' . mysql_result($result,$i,"date") . '</TD><TD>' . mysql_result($result,$i,"value") . '</TD></TR>' . "\n";
                        $i++;
                }
                print "\t\t" . '</TABLE>'  . "\n";
        }
} else {
        print "\nNO RECORDS FOUND\n";
}
print "<!-- $SQL -->";
print "\t" . '</body>' . "\n";
print '</html>' . "\n";
?>

