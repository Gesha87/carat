<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $versions array */
/* @var $apps array */
/* @var $series array */
/* @var $model \backend\models\BugFilter */
/* @var $data \backend\data\MongoArrayDataProvider */

cakebake\bootstrap\select\BootstrapSelectAsset::register($this, [
	'selector' => '.selectpicker',
	'menuArrow' => true,
	'tickIcon' => false,
	'selectpickerOptions' => [
		'style' => 'btn-info form-control',
		'noneSelectedText' => '',
	],
]);
$this->title = Yii::t('app', 'TITLE_BUGS');
$groupBy = $model->group;

$form = \yii\bootstrap\ActiveForm::begin([
	'id' => 'form-filter',
	'method' => 'get',
	'action' => '/dashboard?',
	'layout' => 'inline',
	'fieldConfig' => [
		'template' => "{beginWrapper}\n{input}\n{endWrapper}",
	],
]);
	echo $form->field($model, 'app')->dropDownList($apps, [
		'class' => 'selectpicker',
		'onchange' => '$("#form-filter").submit()',
	]);
	echo '&nbsp;';
	echo $form->field($model, 'group')->radioList(['hash' => Yii::t('app', 'BUG_FILTER_GROUP_HASH'), 'hash_mini' => Yii::t('app', 'BUG_FILTER_GROUP_HASH_MINI')], [
		'onchange' => '$("#form-filter").submit()',
		'data-toggle'=>'buttons',
		'class' => 'btn-group',
		'item' => function ($index, $label, $name, $checked, $value) {
			return Html::radio($name, $checked, [
				'value' => $value,
				'label' => $label,
				'labelOptions' => [
					'class' => 'btn btn-default' . ($checked ? ' active' : '')
				]
			]);
		}
	]);
	echo '&nbsp;';
	echo $form->field($model, 'version')->dropDownList($versions, [
		'prompt' => Yii::t('app', 'BUG_FILTER_VERSION_PROMPT'),
		'class' => 'selectpicker',
		'onchange' => '$("#form-filter").submit()',
	]);
	echo '&nbsp;';
	echo $form->field($model, 'period')->dropDownList([
		'day' => Yii::t('app', 'BUG_FILTER_PERIOD_DAY'),
		'week' => Yii::t('app', 'BUG_FILTER_PERIOD_WEEK'),
		'month' => Yii::t('app', 'BUG_FILTER_PERIOD_MONTH')
	], [
		'prompt' => Yii::t('app', 'BUG_FILTER_PERIOD_PROMPT'),
		'class' => 'selectpicker',
		'onchange' => '$("#form-filter").submit()',
	]);
	echo $form->field($model, 'search')->textInput([
		'placeholder' => Yii::t('app', 'BUG_FILTER_SEARCH_PLACEHOLDER'),
	]);
$form->end();
echo \miloschuman\highcharts\Highstock::widget([
	'id' => 'chart',
	'options' => [
		'navigator' => ['enabled' => false],
		'scrollbar' => ['enabled' => false],
		'navigation' => ['buttonOptions' => []],
		'title' => ['text' => Yii::t('app', 'DASHBOARD_CHART_TITLE')],
		'rangeSelector' => false,
		'colors' => ['#DB343D'],
		'xAxis' => [
			'dateTimeLabelFormats' => ['hour' => ' ']
		],
		'yAxis' => [
			'opposite' => false,
			'title' => ['text' => Yii::t('app', 'DASHBOARD_CHART_Y_TITLE')]
		],
		'series' => $series,
		'plotOptions' => [
			'spline' => [
				'animation' => false,
				'dataLabels' => ['enabled' => true],
				'dataGrouping'=> ['approximation'=> 'sum'],
				'enableMouseTracking'=> false,
				'shadow'=> true
			],
		],
	]
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
		['attribute' => 'stm', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_BUG'), 'format' => 'raw', 'value' => function($model) use ($groupBy) {
			return Html::a(Html::tag('pre', $model['stm'], [
				'class' => 'stack-trace-mini',
				'data-toggle' => 'tooltip',
				'title' => $model['stm'],
			]), Url::toRoute(['site/bug',
				'hash' => $groupBy == 'hash' ? $model['hash'] : $model['hash_mini'],
				'useful' => $groupBy == 'hash' ? 0 : 1,
			]));
		}],
		['attribute' => 'avn', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_VERSION_NAME'), 'format' => 'text'],
		['attribute' => 'avc', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_VERSION_CODE'), 'format' => 'text'],
		['attribute' => 'ucd', 'label' => Yii::t('app', 'DASHBOARD_TABLE_HEADER_CRASHED'), 'format' => 'raw', 'value' => function($model) {
			$time = is_object($model['ucd']) ? $model['ucd']->sec : 0;
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
	'rowOptions' => function ($model, $key, $index, $grid) {
		return ['class' => $model['res'] > 0 ? ($model['res'] >= $model['avc'] ? 'alert-success' : 'alert-danger') : ''];
	}
]);
?>

