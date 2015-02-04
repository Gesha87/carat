<?php
namespace frontend\controllers;

use Yii;
use yii\helpers\Json;
use yii\mongodb\Collection;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Api controller
 */
class ApiController extends Controller
{
	public $modelNameForModelIdentifier = [
		'iPhone1,1' => 'iPhone 1G',
		'iPhone1,2' => 'iPhone 3G',
		'iPhone2,1' => 'iPhone 3GS',
		'iPhone3,1' => 'iPhone 4 (GSM)',
		'iPhone3,2' => 'iPhone 4 (GSM Rev A)',
		'iPhone3,3' => 'iPhone 4 (CDMA)',
		'iPhone4,1' => 'iPhone 4S',
		'iPhone5,1' => 'iPhone 5 (GSM)',
		'iPhone5,2' => 'iPhone 5 (Global)',
		'iPhone5,3' => 'iPhone 5c (GSM)',
		'iPhone5,4' => 'iPhone 5c (Global)',
		'iPhone6,1' => 'iPhone 5s (GSM)',
		'iPhone6,2' => 'iPhone 5s (Global)',
		'iPhone7,1' => 'iPhone 6 Plus',
		'iPhone7,2' => 'iPhone 6',

		'iPad1,1' => 'iPad 1G',
		'iPad2,1' => 'iPad 2 (Wi-Fi)',
		'iPad2,2' => 'iPad 2 (GSM)',
		'iPad2,3' => 'iPad 2 (CDMA)',
		'iPad2,4' => 'iPad 2 (Rev A)',
		'iPad3,1' => 'iPad 3 (Wi-Fi)',
		'iPad3,2' => 'iPad 3 (GSM)',
		'iPad3,3' => 'iPad 3 (Global)',
		'iPad3,4' => 'iPad 4 (Wi-Fi)',
		'iPad3,5' => 'iPad 4 (GSM)',
		'iPad3,6' => 'iPad 4 (Global)',

		'iPad4,1' => 'iPad Air (Wi-Fi)',
		'iPad4,2' => 'iPad Air (Cellular)',
		'iPad5,3' => 'iPad Air 2 (Wi-Fi)',
		'iPad5,4' => 'iPad Air 2 (Cellular)',

		'iPad2,5' => 'iPad mini 1G (Wi-Fi)',
		'iPad2,6' => 'iPad mini 1G (GSM)',
		'iPad2,7' => 'iPad mini 1G (Global)',
		'iPad4,4' => 'iPad mini 2G (Wi-Fi)',
		'iPad4,5' => 'iPad mini 2G (Cellular)',
		'iPad4,7' => 'iPad mini 3G (Wi-Fi)',
		'iPad4,8' => 'iPad mini 3G (Cellular)',
		'iPad4,9' => 'iPad mini 3G (Cellular)',

		'iPod1,1' => 'iPod touch 1G',
		'iPod2,1' => 'iPod touch 2G',
		'iPod3,1' => 'iPod touch 3G',
		'iPod4,1' => 'iPod touch 4G',
		'iPod5,1' => 'iPod touch 5G',
	];

	public function init()
	{
		parent::init();
		Yii::$app->response->data = [
			'data' => null,
			'error' => [
				'code' => 0,
				'message' => '',
			],
		];
	}

    public function actionSend()
    {
		$acraParams = Yii::$app->request->post();
		if (isset($acraParams['PACKAGE_NAME'], $acraParams['STACK_TRACE'], $acraParams['APP_VERSION_NAME'], $acraParams['APP_VERSION_CODE'], $acraParams['USER_CRASH_DATE'])) {
			$fullInfo = json_encode($acraParams);
			$packageName = $acraParams['PACKAGE_NAME'];
			$stackTrace = $acraParams['STACK_TRACE'];
			$stack = explode("\n", $stackTrace);
			$stackTraceMini = preg_replace('/:.+/', '', $stack[0], 1);
			$correctable = false;
			foreach ($stack as $line) {
				if (strpos($line, 'at '.$packageName) !== false) {
					$stackTraceMini .= "\n...".$line;
					$correctable = true;
					break;
				}
			}
			$hashMini = md5($stackTraceMini);
			$hash = md5($stackTrace);
			$appVersionName = $acraParams['APP_VERSION_NAME'];
			$appVersionCode = (int)$acraParams['APP_VERSION_CODE'];
			$userCrashDate = new \MongoDate(strtotime($acraParams['USER_CRASH_DATE']));
			$document = [
				'package_name' => $packageName,
				'hash' => $hash,
				'hash_mini' => $hashMini,
				'stack_trace' => iconv('UTF-8', 'UTF-8//IGNORE', $stackTrace),
				'stack_trace_mini' => iconv('UTF-8', 'UTF-8//IGNORE', $stackTraceMini),
				'app_version_name' => $appVersionName,
				'app_version_code' => $appVersionCode,
				'user_crash_date' => $userCrashDate,
				'full_info' => iconv('UTF-8', 'UTF-8//IGNORE', $fullInfo),
				'resolved' => 0,
			];
			$customData = (string)@$acraParams['CUSTOM_DATA'];
			if (strpos($customData, 'logType = info') !== false) {
				$document['info'] = 1;
			}
			if ($correctable) {
				$document['correctable'] = 1;
			}
			/* @var $collection Collection */
			$collection = Yii::$app->mongodb->getCollection('crash');
			$collection->insert($document);
			Yii::$app->response->data['data'] = [
				'status' => true
			];
		} else {
			Yii::$app->response->data['error'] = [
				'code' => 1,
				'message' => 'Wrong params',
			];
		}
    }

	public function actionSendApple()
	{
		$xmlstring = Yii::$app->request->post('xmlstring');
		if ($xmlstring) {
			$crashes = simplexml_load_string($xmlstring);
			foreach ($crashes->crash as $crash) {
				$appName = (string)$crash->applicationname;
				$log = $fullLog = (string)$crash->log;
				$appVersion = (string)$crash->version;
				$model = (string)$crash->platform;
				if (isset($this->modelNameForModelIdentifier[$model])) {
					$model = $this->modelNameForModelIdentifier[$model] . ' (' . $model . ')';
				}
				$systemVersion = (string)$crash->systemversion;
				$miniLog = '';
				preg_match('/(0x[0-9a-f]+)\s+-\s+0x[0-9a-f]+\s+\+?'.$appName.'\s+(.+)\s+<([0-9a-f]+)>/', $log, $matches);
				$log = substr($log, 0, strpos($log, 'Binary Images:'));
				if ($matches) {
					$loadAddress = $matches[1];
					$architecture = $matches[2];
					$uuid = $matches[3];
					$fileName = Yii::$app->redis->hget('uuid.to.dsym', $uuid);
					$count = preg_match_all('/\n\d+\s+'.$appName.'+\s+(0x[0-9a-f]+)\s+.+/', $log, $addressMatches);
					if ($count) {
						$linesMini = $addressMatches[0];
						$linesMini = array_map('trim', $linesMini);
						$addresses = implode(' ', $addressMatches[1]);
						unset($output);
						if ($fileName) {
							$fileName = Yii::getAlias('@app/data/dSYMs').'/'.$fileName;
							exec(escapeshellcmd("sudo /usr/bin/atosl -A $architecture -o $fileName -l $loadAddress $addresses"), $output);
							if ($output) {
								foreach ($output as $i => $line) {
									$address = @$addressMatches[1][$i];
									if ($address && strcmp($address, $line)) {
										$log = preg_replace('/(\n\d+\s+'.$appName.'+\s+'.$address.'\s+).+/', '$1' . $line, $log, 1);
										$linesMini[$i] = preg_replace('/('.$address.'\s+).+/', '$1' . $line, $linesMini[$i], 1);
									}
								}
							}
						}
						$miniLog = implode("\n", $linesMini);
						$miniLog = preg_replace(['/[\t\p{Zs}]+/', '0x[0-9a-f]+'], [' ', 'addr'], $miniLog);
					}
				}
				$userCrashDate = new \MongoDate(time());
				preg_match('/Date\/Time:\s+(.*)/', $log, $matches);
				if ($matches) {
					$userCrashDate = new \MongoDate(strtotime($matches[1]));
				}
				$fullInfo = [
					'crash' => $fullLog,
					'model' => $model,
					'systemVersion' => $systemVersion,
					'appVersion' => $appVersion,
					'userCrashDate' => $userCrashDate->sec,
				];
				$document = [
					'package_name' => $appName . ' (iPhone)',
					'hash' => md5($log),
					'hash_mini' => md5($miniLog),
					'stack_trace' => $log,
					'stack_trace_mini' => $miniLog,
					'app_version_name' => $appVersion,
					'app_version_code' => $appVersion,
					'user_crash_date' => $userCrashDate,
					'full_info' => Json::encode($fullInfo),
					'resolved' => 0,
					'correctable' => 1,
					'device_id' => 1,
				];
				/* @var $collection Collection */
				$collection = Yii::$app->mongodb->getCollection('crash');
				$collection->insert($document);
				Yii::$app->response->data['data'] = [
					'status' => true
				];
			}
		} else {
			Yii::$app->response->data['error'] = [
				'code' => 1,
				'message' => 'Missing "xmlstring" param!',
			];
		}
	}

	public function actionLoadDsym()
	{
		$dwarfdump = Yii::$app->request->post('dwarfdump');
		$count = preg_match_all('/UUID: ([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\s+/i', $dwarfdump, $matches);
		if (!$count) {
			throw new BadRequestHttpException("Couldn't get uuids");
		}
		$uuids = [];
		foreach ($matches[1] as $uuid) {
			$uuids[] = strtolower(strtr($uuid, ['-' => '']));
		}
		$file = UploadedFile::getInstanceByName('dsym');
		if (!$file) {
			throw new BadRequestHttpException("File not found");
		}
		$uuid = reset($uuids);
		$fileName = date('Y_m_d_') . $uuid;
		$dir = Yii::getAlias('@app/data/dSYMs/');
		if (!file_exists($dir)) {
			mkdir($dir, 0777);
		}
		if ($file->saveAs($dir.'/'.$fileName)) {
			foreach ($uuids as $uuid) {
				Yii::$app->redis->hset('uuid.to.dsym', $uuid, $fileName);
			}
		} else {
			throw new HttpException(500, "Couldn't save file");
		}
	}
}
