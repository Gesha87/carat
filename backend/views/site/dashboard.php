<?php
use kartik\daterange\DateRangePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

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

$form = ActiveForm::begin([
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
	echo $form->field($model, 'info')->radioList([0 => Yii::t('app', 'BUG_FILTER_TYPE_BUG'), 1 => Yii::t('app', 'BUG_FILTER_TYPE_INFO')], [
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
	echo '<br>';
	echo $form->field($model, 'dateRange')->widget(DateRangePicker::className(), [
		'readonly' => true,
		'presetDropdown' => true,
		//'hideInput' => true,
		'convertFormat' => true,
		'callback' => 'function() { $("#form-filter").submit(); }',
		'options' => [
			'placeholder' => Yii::t('app', 'BUG_FILTER_PERIOD_PROMPT'),
		],
		'pluginOptions' => [
			'separator' => ' - ',
			'format' => 'Y-m-d',
			'maxDate' => date('Y-m-d'),
		],
	]);
	echo '&nbsp;';
	echo Html::beginTag('div', ['class' => 'form-group']);
		echo Html::button(Yii::t('app', 'BUG_FILTER_DATE_RANGE_CLEAR'), [
			'class' => 'btn btn-warning',
			'onclick' => '$("#bugfilter-daterange").val(""); $("#form-filter").submit();',
		]);
		echo Html::submitButton(Yii::t('app', 'BUG_FILTER_SUBMIT'), [
			'class' => 'hidden',
		]);
	echo Html::endTag('div');
	echo '&nbsp;';
	echo Html::beginTag('div', ['class' => 'form-group form-group-checkbox']);
		echo Html::activeCheckbox($model, 'correctable', [
			'label' => Yii::t('app', 'BUG_FILTER_IS_CORRECTABLE'),
			'onchange' => '$("#form-filter").submit()',
			'labelOptions' => ['class' => 'checkbox']
		]);
	echo Html::endTag('div');
	echo '&nbsp;';
	echo Html::beginTag('div', ['class' => 'form-group form-group-checkbox']);
		echo Html::activeCheckbox($model, 'hideResolved', [
			'label' => Yii::t('app', 'BUG_FILTER_HIDE_RESOLVED'),
			'onchange' => '$("#form-filter").submit()',
			'labelOptions' => ['class' => 'checkbox']
		]);
	echo Html::endTag('div');
	echo $form->field($model, 'search')->textInput([
		'placeholder' => Yii::t('app', 'BUG_FILTER_SEARCH_PLACEHOLDER'),
	]);
	echo $form->field($model, 'searchNot')->textInput([
		'placeholder' => Yii::t('app', 'BUG_FILTER_SEARCHNOT_PLACEHOLDER'),
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

echo $this->render('_bugs', ['data' => $data, 'model' => $model]);

