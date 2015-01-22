<?php
namespace frontend\controllers;

use Yii;
use yii\mongodb\Collection;
use yii\web\Controller;
use yii\web\Response;

/**
 * Api controller
 */
class ApiController extends Controller
{
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
}
