<?php

use WHMCS\Database\Capsule;

if ( ! defined( "WHMCS" ) ) {
	die( "This file cannot be accessed directly" );
}

$reportdata['title']       = "Годовой отчет о доходах за " . $currentyear;
$reportdata['description'] = "В этом отчете показаны полученные доходы с разбивкой по месяцам в пересчете на базовую валюту по курсу на момент совершения операции.";

$reportdata['yearspagination'] = true;

$currency = getCurrency( 0, 1 );

$reportdata['tableheadings'] = array(
	"Месяц",
	"Сумма в",
	"сборы",
	"торговый посредник",
	"Выведенная сумма",
	"Остаток средств"
);

$groupList = [ 0 => 'Без группы' ];

foreach ( Capsule::table( 'tblclientgroups' )->get( [ 'id', 'groupname' ] ) as $group ) {
	$groupList[ $group->id ] = $group->groupname . PHP_EOL;
}


if ( array_key_exists( 'groupFilter', $_GET ) ) {
	$groupAllow = $_GET['groupFilter'];
}

$reportvalues = array();

if ( empty( $groupAllow ) ) {
	$query = "SELECT date_format(date,'%m'), date_format(date,'%Y'), SUM(amountin/rate), SUM(fees/rate), SUM(amountout/rate) FROM tblaccounts  WHERE date>='" . ( $currentyear - 2 ) . "-01-01'  GROUP BY date_format(date,'%M %Y') ORDER BY date ASC";
} else {
	$query = "SELECT date_format(date,'%m'), date_format(date,'%Y'), SUM(amountin/rate), SUM(fees/rate), SUM(amountout/rate) FROM tblaccounts LEFT JOIN tblclients ON  tblaccounts.userid = tblclients.id WHERE date>='" . ( $currentyear - 2 ) . "-01-01' and groupid IN (" . implode( ',', $groupAllow ) . ") GROUP BY date_format(date,'%M %Y') ORDER BY date ASC";
}

$result = full_query( $query );

while ( $data = mysql_fetch_array( $result ) ) {

	$month          = (int) $data[0];
	$year           = (int) $data[1];
	$amountin       = $data[2];
	$fees           = $data[3];
	$amountout      = $data[4];
	$monthlybalance = $amountin - $fees - $amountout;

	$reportvalues[ $year ][ $month ] = array( $amountin, $fees, $amountout, $monthlybalance );

}

foreach ( $months as $k => $monthName ) {

	if ( $monthName ) {

		$amountin       = $reportvalues[ $currentyear ][ $k ][0];
		$fees           = $reportvalues[ $currentyear ][ $k ][1];
		$amountout      = $reportvalues[ $currentyear ][ $k ][2];
		$monthlybalance = $reportvalues[ $currentyear ][ $k ][3];

		$reportdata['tablevalues'][] = array(
			$monthName . ' ' . $currentyear,
			formatCurrency( $amountin ),
			formatCurrency( $fees ),
			0,
			formatCurrency( $amountout ),
			formatCurrency( $monthlybalance ),
		);

		$overallbalance += $monthlybalance;

	}

}

$reportdata['footertext'] = '<p align="center"><strong>Баланс: ' . formatCurrency( $overallbalance ) . '</strong></p>';

$chartdata['cols'][] = array( 'label' => 'Days Range', 'type' => 'string' );
$chartdata['cols'][] = array( 'label' => $currentyear - 2, 'type' => 'number' );
$chartdata['cols'][] = array( 'label' => $currentyear - 1, 'type' => 'number' );
$chartdata['cols'][] = array( 'label' => $currentyear, 'type' => 'number' );

for ( $i = 1; $i <= 12; $i ++ ) {
	$chartdata['rows'][] = array(
		'c' => array(
			array(
				'v' => $months[ $i ],
			),
			array(
				'v' => $reportvalues[ $currentyear - 2 ][ $i ][3],
				'f' => formatCurrency( $reportvalues[ $currentyear - 2 ][ $i ][3] )->toFull(),
			),
			array(
				'v' => $reportvalues[ $currentyear - 1 ][ $i ][3],
				'f' => formatCurrency( $reportvalues[ $currentyear - 1 ][ $i ][3] )->toFull(),
			),
			array(
				'v' => $reportvalues[ $currentyear ][ $i ][3],
				'f' => formatCurrency( $reportvalues[ $currentyear ][ $i ][3] )->toFull(),
			),
		),
	);
}

$args              = array();
$args['colors']    = '#3070CF,#F9D88C,#cb4c30';
$args['chartarea'] = '80,20,90%,350';

$reportdata["headertext"] = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/css/bootstrap-select.min.css">' .
                            '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/js/bootstrap-select.min.js"></script>' .
                            '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.5/js/i18n/defaults-ru_RU.min.js"></script>' .
                            '<form id="groupFilter" method="get">' .
                            '<input type="hidden" name="report" value="Annual_Income_Report_MODIFY">' .
                            '<select name="groupFilter[]" class="selectpicker" data-width="420px" data-actions-box="true" data-header="Для каких групп клиентов отображать статистику ?" title="Выберите группы клиентов для отображения по ним статистики" multiple data-live-search="true">' .
                            '';
foreach ( $groupList as $groupID => $groupName ) {
	if ( empty( $groupAllow ) ) {
		$reportdata["headertext"] .= '<option value="' . $groupID . '" selected>' . $groupName . '</option>';
	} else {
		if ( in_array( $groupID, $groupAllow ) ) {
			$reportdata["headertext"] .= '<option value="' . $groupID . '" selected>' . $groupName . '</option>';
		} else {
			$reportdata["headertext"] .= '<option value="' . $groupID . '"  >' . $groupName . '</option>';
		}
	}
}
$reportdata["headertext"] .= '</select><br/><br/>';
$reportdata["headertext"] .= '</form>' .
                             '<script>' .
                             '$(\'.selectpicker\').selectpicker();' .
                             '$(\'.selectpicker\').on(\'hidden.bs.select\', function (e, clickedIndex, isSelected, previousValue) { $(\'#groupFilter\').submit();});' .
                             '</script>';

$reportdata['headertext'] .= $chart->drawChart( 'Column', $chartdata, $args, '400px' );
