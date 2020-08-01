<?php

use WHMCS\Billing\Payment\Transaction;
use WHMCS\Database\Capsule;

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

$reportdata["title"] = "Популярные платежные шлюзы";
$reportdata["description"] = "Рейтинг популярных платежных шлюзов";

$reportdata["tableheadings"] = array("Название шлюза", "количество");
$pmonth = str_pad((int)$month, 2, "0", STR_PAD_LEFT);


$results = Transaction::select(Capsule::raw('sum(amountin) as sum,gateway,date'))->where('date', 'LIKE', (int)$year . "-" . $pmonth . '-%')->groupBy('gateway')->get();
$sql = Transaction::select(Capsule::raw('sum(amountin) as sum,gateway,date'))->where('date', 'LIKE', (int)$year . "-" . $pmonth . '-%')->groupBy('gateway')->toSql();
foreach ($results as $result) {
    $reportdata["tablevalues"][] = array($result['gateway'], $result['sum']);
    $chartdata['rows'][] = ['c' => array(array('v' => $result['gateway']), array('v' => $result['sum']))];
}

$reportdata["footertext"] = "<p>* denotes converted to default currency</p>";

$chartdata['cols'][] = array('label' => 'Name', 'type' => 'string');
$chartdata['cols'][] = array('label' => 'Count', 'type' => 'number');

$args = array();
$args['legendpos'] = 'right';

$reportdata["headertext"] = $chart->drawChart('Pie', $chartdata, $args, '300px');
$reportdata["monthspagination"] = true;

?>