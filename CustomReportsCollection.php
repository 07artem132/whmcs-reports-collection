<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 16.12.2018
 * Time: 21:51
 */

use WHMCS\Database\Capsule;


function CustomReportsCollection_config() {
	$config = [
		"name"        => "Расширенная система отчетов",
		"description" => "",
		"version"     => "1",
		"author"      => "service-voice",
		"fields"      => []
	];

	return $config;
}


function CustomReportsCollection_activate() {
	if ( Capsule::table( 'tbladdonmodules' )->where( 'module', '=', 'GameServersAthpStats' )->count() === 0 ) {
		return array(
			'status'      => 'error',
			'description' => 'Для активации модуля требуется модуль "GameServersAthpStats"'
		);
	}

	foreach ( scandir( ROOTDIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'CustomReportsCollection' . DIRECTORY_SEPARATOR . 'reports' ) as $report ) {
		if ( $report === '.' || $report === '..' ) {
			continue;
		}

		$target = ROOTDIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'CustomReportsCollection' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $report;
		$link   = ROOTDIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $report;

		if ( ! link( $target, $link ) ) {
			return array(
				'status'      => 'error',
				'description' => 'Ошибка при создании ссылки на отчет ' . $report
			);
		}

	}

	return array(
		'status'      => 'success',
		'description' => 'Отчеты успешно добавлены'
	);

}

function CustomReportsCollection_deactivate() {
	foreach ( scandir( ROOTDIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'CustomReportsCollection' . DIRECTORY_SEPARATOR . 'reports' ) as $report ) {
		if ( $report === '.' || $report === '..' ) {
			continue;
		}

		$link   = ROOTDIR . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $report;

		if ( ! unlink(   $link ) ) {
			return array(
				'status'      => 'error',
				'description' => 'Ошибка при удалении ссылки на отчет ' . $report
			);
		}

	}

	return array(
		'status'      => 'success',
		'description' => 'Отчеты успешно удалены'
	);
}
