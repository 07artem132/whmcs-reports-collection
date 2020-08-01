<?php

use WHMCS\Billing\Payment\Transaction;
use WHMCS\Database\Capsule;

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

$reportdata["title"] = "Популярные платежные шлюзы";
$reportdata["description"] = "Рейтинг популярных платежных шлюзов";

$reportdata["tableheadings"] = array("Название шлюза", "количество");
$results = Transaction::select(Capsule::raw('count(*) as count,gateway'))->groupBy('gateway')->get();
foreach ($results as $result)
{
   $reportdata["tablevalues"][] = array($result['gateway'], $result['count']);
    $chartdata['rows'][] = ['c' => array(array('v' => $result['gateway']), array('v' => $result['count']))];
}

$reportdata["footertext"] = "<p>* denotes converted to default currency</p>";

$chartdata['cols'][] = array('label' => 'Name', 'type' => 'string');
$chartdata['cols'][] = array('label' => 'Count', 'type' => 'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Pie', $chartdata, $args, '300px');

?>