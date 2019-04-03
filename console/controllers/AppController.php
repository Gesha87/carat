<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\mongodb\Connection;

class AppController extends Controller
{
	public function actionClear($d = 30)
	{
		/* @var $db Connection */
		$db = Yii::$app->mongodb;
		$collection = $db->getCollection('crash');
		$redis = Yii::$app->redis;

		$versionsByApp = $collection->aggregate([
			[
				'$group' => [
					'_id' => '$package_name',
					'versions' => ['$addToSet' => '$app_version_code']
				]
			]
		]);
		$nor = [];
		foreach ($versionsByApp as $v) {
			$package = $v['_id'];
			if ($package) {
				$max = 0;
				foreach ($v['versions'] as $version) {
					if ($version > $max) $max = $version;
				}
				$nor[] = ['package_name' => $package, 'app_version_code' => $max];
			}
		}

		$edge = time() - 3600 * 24 * $d;
		$condition =  ['user_crash_date' => ['$lt' => new \MongoDB\BSON\UTCDateTime($edge * 1000)]];
		if ($nor) {
			$condition['$nor'] = $nor;
		}
		$hashes = $collection->distinct('hash_mini', $condition);
		Yii::info('Found ' . count($hashes) . ' hashes to delete', 'acra');
		if ($hashes) {
			$params = ['resolved.bugs'];
			$params = array_merge($params, $hashes);
			$resolves = call_user_func_array([$redis, 'hmget'], $params);
			$i = 0;
			foreach ($hashes as $i => $hash) {
				$collection->update(['hash_mini' => $hash], ['resolved' => (int)$resolves[$i]]);
				echo "\r" . ($i + 1) . ' updated';
			}
			echo "\r" . ($i + 1) . " updated\n";
			$collection->remove($condition);
		}
		Yii::info('Complete', 'acra');
	}

	public function actionClearInfo($d = 7)
	{
		/* @var $db Connection */
		$db = Yii::$app->mongodb;
		$collection = $db->getCollection('crash');

		$edge = time() - 3600 * 24 * $d;
		$condition =  [
			'user_crash_date' => ['$lt' => new \MongoDB\BSON\UTCDateTime($edge * 1000)],
			'info' => 1,
		];
		$count = $collection->find($condition)->count();
		Yii::info('Found ' . $count . ' info to delete', 'acra');
		$collection->remove($condition);
		Yii::info('Complete', 'acra');
	}
}