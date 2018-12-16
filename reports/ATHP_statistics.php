<?php

use WHMCS\Module\Addon\GameServersAthpStats\InstanceStatisticsController;

if ( ! defined( "WHMCS" ) ) {
	die( "This file cannot be accessed directly" );
}

$reportdata["title"]       = "Статистика по ATHP";
$reportdata["description"] = " ";
$ipAllow                   = [];
$ipList                    = InstanceStatisticsController::getAllowIpForYear( $year );
$allowMonths               = InstanceStatisticsController::getAvailableMonthsForYear( $year );
$Month_r                   = array(
	"январь",
	"февраль",
	"март",
	"апрель",
	"май",
	"июнь",
	"июль",
	"август",
	"сентябрь",
	"октябрь",
	"ноябрь",
	"декабрь"
);

if ( array_key_exists( 'ipFilter', $_GET ) ) {
	$ipAllow = $_GET['ipFilter'];
}
$reportdata["tableheadings"] = array( "Месяц", "Слоты", "Онлайн", "Процент использования слотов" );

for ( $rawmonth = 1; $rawmonth <= 12; $rawmonth ++ ) {
	$month = str_pad( $rawmonth, 2, 0, STR_PAD_LEFT );

	if ( in_array( $month, $allowMonths ) ) {
		$avgSlotsMonth     = InstanceStatisticsController::getAvgSlotsMonth( $year, $month, $ipAllow );
		$avgOnlineMonth    = InstanceStatisticsController::getAvgOnlineMonth( $year, $month, $ipAllow );
		$OnlineToSlotsRate = round( $avgOnlineMonth * 100 / $avgSlotsMonth, 2 );
	} else {
		$avgSlotsMonth     = 0;
		$avgOnlineMonth    = 0;
		$OnlineToSlotsRate = 0;
	}

	$reportdata["tablevalues"][] = array(
		$Month_r[ $rawmonth - 1 ] . ' ' . $year,
		$avgSlotsMonth,
		$avgOnlineMonth,
		$OnlineToSlotsRate
	);

	$chartdata['rows'][] = array(
		'c' => array(
			array( 'v' => $Month_r[ $rawmonth - 1 ] ),
			array( 'v' => $avgSlotsMonth ),
		)
	);


}

$chartdata['cols'][] = array( 'label' => 'Month', 'type' => 'string' );
$chartdata['cols'][] = array( 'label' => $year, 'type' => 'number' );


$args              = array();
$args['title']     = 'Slots';
$args['colors']    = '#3366CC,#888888';
$args['legendpos'] = 'right';

$reportdata["headertext"] = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/css/bootstrap-select.min.css">' .
                            '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/js/bootstrap-select.min.js"></script>' .
                            '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.5/js/i18n/defaults-ru_RU.min.js"></script>' .
                            '<form id="ipFilter" method="get">' .
                            '<input type="hidden" name="report" value="ATHP_statistics">' .
                            '<select name="ipFilter[]" class="selectpicker" data-width="420px" data-actions-box="true" data-header="Для каких ip отображать статистику ?" title="Выберите IP для отображения по ним статистики" multiple data-live-search="true">';
foreach ( $ipList as $ip ) {
	if ( empty( $ipAllow ) ) {
		$reportdata["headertext"] .= '<option value="' . $ip . '" selected>' . $ip . '</option>';
	} else {
		if ( in_array( $ip, $ipAllow ) ) {
			$reportdata["headertext"] .= '<option value="' . $ip . '" selected>' . $ip . '</option>';
		} else {
			$reportdata["headertext"] .= '<option value="' . $ip . '"  >' . $ip . '</option>';
		}
	}
}
$reportdata["headertext"] .= '</select>' .
                             '</form>' .
                             '<script>' .
                             '$(\'.selectpicker\').selectpicker();' .
                             '$(\'.selectpicker\').on(\'hidden.bs.select\', function (e, clickedIndex, isSelected, previousValue) { $(\'#ipFilter\').submit();});' .
                             '</script>' .
                             $chart->drawChart( 'Area', $chartdata, $args, '400px' );

$reportdata["yearspagination"] = true;