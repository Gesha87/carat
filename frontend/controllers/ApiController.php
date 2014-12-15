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
			$stackTrace = preg_replace('/Bitmap@.+/i', 'Bitmap', $stackTrace, 1);
			$stack = explode("\n", $stackTrace);
			$stackTraceMini = $stack[0];
			foreach ($stack as $line) {
				if (strpos($line, 'at '.$packageName) !== false) {
					$stackTraceMini .= "\n...".$line;
					break;
				}
			}
			$hashMini = md5($stackTraceMini);
			$hash = md5($stackTrace);
			$appVersionName = $acraParams['APP_VERSION_NAME'];
			$appVersionCode = (int)$acraParams['APP_VERSION_CODE'];
			$userCrashDate = new \MongoDate(strtotime($acraParams['USER_CRASH_DATE']));
			/* @var $collection Collection */
			$collection = Yii::$app->mongodb->getCollection('crash');
			$collection->insert([
				'package_name' => $packageName,
				'hash' => $hash,
				'hash_mini' => $hashMini,
				'stack_trace' => $stackTrace,
				'stack_trace_mini' => $stackTraceMini,
				'app_version_name' => $appVersionName,
				'app_version_code' => $appVersionCode,
				'user_crash_date' => $userCrashDate,
				'full_info' => $fullInfo
			]);
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
