<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_windrose.php');

// Data can be specified using both ordinal index of the axis
// as well as the direction label
$data = array(
    0 => array(10,50,40));
//    1 => array(3,4,1,4),
//    'WSW' => array(1,5,5,3),
//    'N' => array(2,3,8,1,1),
//    15 => array(2,3,5));

// First create a new windrose graph with a title
$graph = new WindroseGraph(600,600);
$graph->title->Set('A basic Windrose graph');

// Create the windrose plot.
$wp = new WindrosePlot($data);
$wp->SetRanges(array(0,1.8,3.6,7.2,14.4,28.8,100));

// Add and send back to browser
$graph->Add($wp);
$graph->Stroke();
?>

