<?php

use WHMCS\Module\Addon\GameServersAthpStats\VirtualServerStatisticsController;
use WHMCS\Module\Addon\GameServersAthpStats\InstanceStatisticsController;

if ( ! defined( "WHMCS" ) ) {
	die( "This file cannot be accessed directly" );
}

$reportdata["title"]       = "Эффективность для адаптации на АТХП";
$reportdata["description"] = " ";
$ipAllow                   = [];
$minSlots                  = 20;
$ipList                    = VirtualServerStatisticsController::getAllowIpForLastWeekly();

if ( array_key_exists( 'ipFilter', $_GET ) ) {
	$ipAllow = $_GET['ipFilter'];
}
if ( array_key_exists( 'minSlot', $_GET ) ) {
	$minSlots = $_GET['minSlot'];
}

$reportdata["footertext"] .= '
<table width="100%" class="table table-condensed">
<tbody>
<tr bgcolor="#efefef" style="text-align:center;font-weight:bold;">
<td>ip</td>
<td>uid</td>
<td>Слоты (максимум за 7 дней)</td>
<td>Онлайн (максимум за 7 дней)</td>
<td>Процент использования слотов (за 7 дней)</td>
</tr>';

$slots  = InstanceStatisticsController::getMaxSlotsByVirtualServersLastWeekly( $ipAllow );
$online = InstanceStatisticsController::getMaxOnlineByVirtualServersLastWeekly( $ipAllow );

foreach ( $slots as $ip => $virtualServers ) {
	foreach ( $virtualServers as $uid => $slots ) {
		$maxSlotsWeekly = $slots;

		if ( $minSlots > $maxSlotsWeekly ) {
			continue;
		}

		$maxOnlineWeekly   = $online[ $ip ][ $uid ];
		$OnlineToSlotsRate = round( $maxOnlineWeekly * 100 / $maxSlotsWeekly, 2 );

		if ( $OnlineToSlotsRate > 70 ) {
			$color = '#90EE90';
		} else {
			$color = 'red';
		}

		$reportdata["footertext"] .= '<tr bgcolor="' . $color . '" style="text-align:center;"><td>' . substr( $ip, 0, - 5 ) . '</td><td>' . $uid . '</td><td>' . $maxSlotsWeekly . '</td><td>' . $maxOnlineWeekly . '</td><td>' . $OnlineToSlotsRate . '%</td></tr>';
	}
}

$reportdata["footertext"] .= '</tbody></table>';
$reportdata["headertext"] = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/css/bootstrap-select.min.css">' .
                            '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/js/bootstrap-select.min.js"></script>' .
                            '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.5/js/i18n/defaults-ru_RU.min.js"></script>' .
                            '<form id="ipFilter" method="get">' .
                            '<input type="hidden" name="report" value="ATHP_adaptation_efficiency">' .
                            '<select name="ipFilter[]" class="selectpicker" data-width="420px" data-actions-box="true" data-header="Для каких ip отображать статистику ?" title="Выберите IP для отображения по ним статистики" multiple data-live-search="true">' .
                            '';
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
$reportdata["headertext"] .= '</select>';
$reportdata["headertext"] .= '<br/><br/><div class="form-group col-md-3">
    <label for="exampleInputPassword1">Минимум слотов на сервере для отображения</label>
    <input type="number" class="form-control" id="minSlot" name="minSlot" onchange="$(\'#ipFilter\').submit();" value="'.$minSlots.'" placeholder="Минимум слотов">
  </div>';

$reportdata["headertext"] .= '</form>' .
                             '<script>' .
                             '$(\'.selectpicker\').selectpicker();' .
                             '$(\'.selectpicker\').on(\'hidden.bs.select\', function (e, clickedIndex, isSelected, previousValue) { $(\'#ipFilter\').submit();});' .
                             '</script>';

