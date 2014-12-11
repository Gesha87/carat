<?php
namespace backend\widgets;

use Yii;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;

class LanguageDropdown extends ButtonDropdown
{
	/**
	 * @var boolean whether the dropdown should be dropup :)
	 */
	public $dropup = true;
	/**
	 * @var array the HTML attributes for the widget container tag.
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 */
	public $containerOptions = [];

	public function init()
	{
		$route = Yii::$app->controller->route;
		$appLanguage = Yii::$app->language;
		$params = Yii::$app->getRequest()->getQueryParams();
		$labels = Yii::$app->params['languageLabels'];
		$this->dropdown = [
			'options' => [
				'class' => 'dropdown-menu-right',
			]
		];

		array_unshift($params, $route);

		foreach (Yii::$app->localeUrls->languages as $language) {
			if ($language === $appLanguage) {
				$this->label = isset($labels[$language]) ? $labels[$language] : 'T_T';
			}
			$params['language'] = $language;
			$this->dropdown['items'][] = [
				'label' => isset($labels[$language]) ? $labels[$language] : 'T_T',
				'url' => $params,
			];
		}
		parent::init();
	}

	public function run()
	{
		Html::addCssClass($this->containerOptions, 'btn-group');
		if ($this->dropup) {
			Html::addCssClass($this->containerOptions, 'dropup');
		}
		echo Html::beginTag('div', $this->containerOptions);
		echo "\n" . $this->renderButton();
		echo "\n" . $this->renderDropdown();
		echo "\n" . Html::endTag('div');
		$this->registerPlugin('button');
	}
}