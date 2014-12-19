<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $crash array */

$this->title = Yii::t('app', 'TITLE_CRASH');
echo Html::beginTag('ul', ['class' => 'breadcrumb']);
echo Html::tag('li', Html::a('&#8592; '.Yii::t('app', 'CRASH_LINK_BACK'), 'javascript:history.back();'));
echo Html::endTag('ul');
echo \yii\widgets\DetailView::widget([
	'model' => $crash,
	'template' => function($attribute, $index, $widget) {
		$label = $attribute['label'];
		$value = $widget->formatter->format($attribute['value'], 'html');
		return "<tr><th>$label</th><td>$value</td></tr>";
	}
]);