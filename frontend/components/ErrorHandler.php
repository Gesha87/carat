<?php
namespace frontend\components;

use Yii;
use yii\web\Response;
use yii\web\HttpException;

class ErrorHandler extends \yii\web\ErrorHandler
{
	/**
	 * @inheritdoc
	 */
	protected function renderException($exception)
	{
		if (Yii::$app->has('response')) {
			$response = Yii::$app->getResponse();
		} else {
			$response = new Response();
		}
		if ($exception instanceof HttpException) {
			$response->setStatusCode($exception->statusCode);
		} else {
			$response->setStatusCode(500);
		}
		$response->data = [
			'data' => null,
			'error' => [
				'code' => $response->statusCode,
				'message' => $response->statusText,
			]
		];
		$response->format = Response::FORMAT_JSON;

		$response->send();
	}

}