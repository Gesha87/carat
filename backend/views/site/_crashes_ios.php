<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $data \yii\data\ArrayDataProvider */

\yii\widgets\Pjax::begin([
	'enablePushState' => false,
	'enableReplaceState' => true,
]);
echo \yii\grid\GridView::widget([
	'dataProvider' => $data,
	'layout' => "{pager}\n{items}\n{pager}",
	'tableOptions' => [
		'class' => 'table table-bordered table-condensed',
	],
	'columns' => [
		['attribute' => 'appVersion', 'label' => Yii::t('app', 'BUG_APP_VERSION_NAME'), 'format' => 'text'],
		['attribute' => 'systemVersion', 'label' => Yii::t('app', 'BUG_IOS_VERSION'), 'format' => 'text'],
		['attribute' => 'model', 'label' => Yii::t('app', 'BUG_PHONE_MODEL'), 'format' => 'text'],
		['attribute' => 'userCrashDate', 'label' => Yii::t('app', 'BUG_USER_CRASH_DATE'), 'format' => 'raw', 'value' => function($model) {
			$time = $model['userCrashDate'];
			$time < 0 AND $time = 0;
			return Html::tag('span', '', ['class' => 'glyphicon glyphicon-time']).'&nbsp;'.Yii::$app->formatter->asDatetime($time, 'short');
		}],
		['class' => 'yii\grid\ActionColumn', 'template' => '{view}', 'urlCreator' => function($action, $model) {
			return \yii\helpers\Url::toRoute(['site/crash', 'id' => $model['id']]);
		}]
	],
	'rowOptions' => function ($model, $key, $index, $grid) {
		return ['class' => $model['resolved'] ? 'alert-success' : ''];
	}
]);
\yii\widgets\Pjax::end();