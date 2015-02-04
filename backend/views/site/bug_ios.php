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
	$packageName = $row['pn'];
	echo Html::beginTag('pre');
	foreach ($stack as $line) {
		if (preg_match('/\d+\s+'.$packageName.'+\s+(0x[0-9a-f]+)\s/', $line)) $line = '<span class="alert-success">'.$line.'</span>';
		echo $line.'<br>';
	}
	echo Html::endTag('pre');
	echo Html::beginForm('', 'get', ['class' => 'well form-horizontal well-sm', 'onsubmit' => 'return false;']);
		$hash = Yii::$app->getRequest()->getQueryParam('hash', '');
		echo Html::checkbox('resolve', (bool)Yii::$app->redis->hget('resolved.bugs', $hash), [
			'label' => Yii::t('app', 'BUG_RESOLVE'),
			'class' => 'resolve',
			'data-attribute' => Yii::$app->getRequest()->getQueryParam('useful') ? 'hash_mini' : 'hash',
			'data-hash' => $hash
		]);
	echo Html::endForm();
}

echo $this->render('_crashes_ios', ['data' => $data]);