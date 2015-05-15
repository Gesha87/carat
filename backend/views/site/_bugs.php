<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $filter \backend\models\BugFilter */
/* @var $data \backend\data\MongoArrayDataProvider */

$groupBy = $filter->group;
Pjax::begin([
	'enablePushState' => false,
	'enableReplaceState' => true,
]);
echo \yii\grid\GridView::widget([
	'dataProvider' => $data,
	'layout' => "{pager}\n{items}\n{pager}",
	'tableOptions' => [
		'class' => 'table table-bordered table-condensed',
	],
	'pager' => [
		'firstPageLabel' => '&laquo;&laquo;',
		'lastPageLabel' => '&raquo;&raquo;',
	],
	'columns' => [
		['attribute' => 'id', 'label' => '#', 'format' => 'text', 'value' => function($model) {
			return substr($model['id'], 0, 6).'...';
		}],
		['attribute' => 'cnt', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_COUNT'), 'format' => 'raw', 'value' => function($model) {
			$class = $model['cnt'] > Yii::$app->params['countDanger'] ? 'alert-danger' : '';
			return "<span class=\"badge $class\">{$model['cnt']}</span>";
		}],
		['attribute' => 'stm', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_BUG'), 'format' => 'raw', 'value' => function($model) use ($groupBy, $filter) {
			return Html::a(Html::tag('pre', $model['stm'], [
				'class' => 'stack-trace-mini',
				'data-toggle' => 'tooltip',
				'title' => $model['stm'],
			]), Url::toRoute(['site/bug',
				'hash' => $groupBy == 'hash' ? $model['hash'] : $model['hash_mini'],
				'useful' => $groupBy == 'hash' ? 0 : 1,
				'app' => $filter->app
			]), ['data-pjax' => 0]);
		}],
		['attribute' => 'avn', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_VERSION_NAME'), 'format' => 'text'],
		['attribute' => 'avc', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_VERSION_CODE'), 'format' => 'text'],
		['attribute' => 'ucd', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_CRASHED'), 'format' => 'raw', 'value' => function($model) {
			$time = is_object($model['ucd']) ? $model['ucd']->sec : 0;
			$time < 0 AND $time = 0;
			return '<span class="glyphicon glyphicon-time"></span>&nbsp;'.Yii::$app->formatter->asDatetime($time, 'short');
		}],
		['attribute' => 'res', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_RESOLVE'), 'format' => 'raw', 'value' => function($model) use ($groupBy) {
			return Html::checkbox('resolve', $model['res'] > 0, [
				'class' => 'resolve',
				'data-version' => $model['avc'],
				'data-attribute' => $groupBy == 'hash' ? 'hash' : 'hash_mini',
				'data-hash' => $groupBy == 'hash' ? $model['hash'] : $model['hash_mini']
			]);
		}],
	],
	'rowOptions' => function ($model, $key, $index, $grid) use ($groupBy) {
		return ['class' => $model['res'] > 0 ? ($model['res'] >= $model['avc'] ? 'alert-success' : 'alert-danger') : ''];
	}
]);
Pjax::end();