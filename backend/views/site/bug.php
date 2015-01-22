<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $data \yii\data\ArrayDataProvider */

$this->title = Yii::t('app', 'TITLE_BUG');
echo Html::beginTag('ul', ['class' => 'breadcrumb']);
	echo Html::tag('li', Html::a('&#8592; '.Yii::t('app', 'BUG_LINK_BACK'), 'javascript:history.back();'));
echo Html::endTag('ul');
if ($row = reset($data->allModels)) {
	echo Html::tag('h2', Yii::t('app', 'BUG_STACK_TRACE'));
	$stack = explode("\n", $row['st']);
	$packageName = $row['PACKAGE_NAME'];
	echo Html::beginTag('div', ['class' => 'well']);
	foreach ($stack as $line) {
		$line = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $line);
		if (strpos($line, 'at '.$packageName) !== false) $line = '<span class="alert-success">'.$line.'</span>';
		echo $line.'<br>';
	}
	echo Html::endTag('div');
	echo Html::beginForm('', 'get', ['class' => 'well form-horizontal well-sm', 'onsubmit' => 'return false;']);
		$hash = Yii::$app->getRequest()->getQueryParam('hash', '');
		echo Html::checkbox('resolve', Yii::$app->redis->sismember('resolved.bugs', $hash), [
			'label' => Yii::t('app', 'BUG_RESOLVE'),
			'class' => 'resolve',
			'data-attribute' => Yii::$app->getRequest()->getQueryParam('useful') ? 'hash_mini' : 'hash',
			'data-hash' => $hash
		]);
	echo Html::endForm();
}

echo $this->render('_crashes', ['data' => $data]);