<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $data \yii\data\ArrayDataProvider */

$this->title = Yii::t('app', 'TITLE_BUG');
echo Html::beginTag('ul', ['class' => 'breadcrumb']);
	echo Html::tag('li', Html::a('&#8592; '.Yii::t('app', 'BUG_LINK_BACK'), 'javascript:history.back();'));
echo Html::endTag('ul');
$resolved = 0; $version = 0;
foreach ($data->allModels as $row) {
	$row['resolved'] > $resolved AND $resolved = $row['resolved'];
	version_compare($row['APP_VERSION_CODE'], $version, '>') AND $version = $row['APP_VERSION_CODE'];
}
if ($row = reset($data->allModels)) {
	echo Html::tag('h2', Yii::t('app', 'BUG_STACK_TRACE'));
	$stack = explode("\n", $row['STACK_TRACE']);
	$packageName = $row['PACKAGE_NAME'];
	echo Html::beginTag('div', ['class' => 'well']);
	foreach ($stack as $line) {
		$line = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $line);
		if (strpos($line, 'at '.$packageName) !== false) $line = '<span class="alert-success">'.$line.'</span>';
		echo $line.'<br>';
	}
	echo Html::endTag('div');
	echo Html::beginForm('', 'get', ['class' => 'well form-horizontal well-sm', 'onsubmit' => 'return false;']);
		echo Html::checkbox('resolve', $resolved, [
			'label' => Yii::t('app', 'BUG_RESOLVE'),
			'class' => 'resolve',
			'data-version' => $version,
			'data-attribute' => Yii::$app->getRequest()->getQueryParam('useful') ? 'hash_mini' : 'hash',
			'data-hash' => Yii::$app->getRequest()->getQueryParam('hash', '')
		]);
	echo Html::endForm();
}

echo \yii\grid\GridView::widget([
	'dataProvider' => $data,
	'tableOptions' => [
		'class' => 'table table-bordered table-condensed',
	],
	'columns' => [
		['attribute' => 'APP_VERSION_NAME', 'label' => Yii::t('app', 'BUG_APP_VERSION_NAME'), 'format' => 'text'],
		['attribute' => 'APP_VERSION_CODE', 'label' => Yii::t('app', 'BUG_APP_VERSION_CODE'), 'format' => 'text'],
		['attribute' => 'ANDROID_VERSION', 'label' => Yii::t('app', 'BUG_ANDROID_VERSION'), 'format' => 'text'],
		['attribute' => 'PHONE_MODEL', 'label' => Yii::t('app', 'BUG_PHONE_MODEL'), 'format' => 'raw', 'value' => function($model) {
			if (isset($model['PHONE_MODEL'])) {
				$phoneModel = urlencode($model['PHONE_MODEL']);
				$return = Html::a($model['PHONE_MODEL'], 'https://www.google.ru/?q='.$phoneModel.'#newwindow=1&q='.$phoneModel, ['target' => '_blank']);
				if (isset($model['BUILD'])) {
					preg_match('/DEVICE=(.+)/', $model['BUILD'], $matches);
					if (($device = @$matches[1]) && strcmp($device, $model['PHONE_MODEL'])) {
						$phoneModel = urlencode($device);
						$return .= ' ('.Html::a($device, 'https://www.google.ru/?q='.$phoneModel.'#newwindow=1&q='.$phoneModel, ['target' => '_blank']).')';
					}
				}
				return $return;
			}

			return null;
		}],
		['attribute' => 'USER_CRASH_DATE', 'label' => Yii::t('app', 'BUG_USER_CRASH_DATE'), 'format' => 'raw', 'value' => function($model) {
			return Html::tag('span', '', ['class' => 'glyphicon glyphicon-time']).'&nbsp;'.Yii::$app->formatter->asDatetime(strtotime($model['USER_CRASH_DATE']), 'short');
		}],
		['attribute' => 'CRASH_CONFIGURATION', 'label' => Yii::t('app', 'BUG_CRASH_CONFIGURATION'), 'format' => 'raw', 'value' => function($model) {
			preg_match('/locale=(.*)/', @$model['CRASH_CONFIGURATION'], $matches);
			return (string)@$matches[1];
		}],
		['attribute' => 'DISPLAY', 'label' => Yii::t('app', 'BUG_DISPLAY'), 'format' => 'text', 'value' => function($model) {
			preg_match('/0\.height=(\d+)/', @$model['DISPLAY'], $matches);
			$height = (int)@$matches[1];
			preg_match('/0\.width=(\d+)/', @$model['DISPLAY'], $matches);
			$width = (int)@$matches[1];
			return '['.$width.','.$height.']';
		}],
		['attribute' => 'AVAILABLE_MEM_SIZE', 'label' => Yii::t('app', 'BUG_AVAILABLE_MEM_SIZE'), 'format' => 'text', 'value' => function($model) {
			return round(@$model['AVAILABLE_MEM_SIZE']/1048576, 2) . ' MB';
		}],
		['attribute' => 'TOTAL_MEM_SIZE', 'label' => Yii::t('app', 'BUG_TOTAL_MEM_SIZE'), 'format' => 'text', 'value' => function($model) {
			return round(@$model['TOTAL_MEM_SIZE']/1048576, 2) . ' MB';
		}],
		['attribute' => 'CUSTOM_DATA', 'label' => Yii::t('app', 'BUG_CUSTOM_DATA'), 'format' => 'raw', 'value' => function($model) {
			return str_replace("\n", '<br>', @$model['CUSTOM_DATA']);
		}],
		['class' => 'yii\grid\ActionColumn', 'template' => '{view}', 'urlCreator' => function($action, $model) {
			return \yii\helpers\Url::toRoute(['site/crash', 'id' => $model['id']]);
		}]
	],
	'rowOptions' => function ($model, $key, $index, $grid) {
		return ['class' => $model['resolved'] ? 'alert-success' : ''];
	}
]);