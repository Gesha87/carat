<?php
namespace backend\components;

use Yii;
use yii\base\InvalidConfigException;

class UrlManager extends \yii\web\UrlManager
{
	/**
	 * @inheritdoc
	 */
	public $enablePrettyUrl = true;
	/**
	 * @var string if a parameter with this name is passed to any `createUrl()` method, the created URL
	 * will use the language specified there. URLs created this way can be used to switch to a different
	 * language. If no such parameter is used, the currently detected application language is used.
	 */
	public $languageParam = 'language';

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if (!$this->enablePrettyUrl) {
			throw new InvalidConfigException('Locale URL support requires enablePrettyUrl to be set to true.');
		}

		parent::init();
	}

	/**
	 * @inheritdoc
	 */
	public function createUrl($params)
	{
		$params = (array)$params;
		$localeUrls = Yii::$app->localeUrls;
		if (isset($params[$this->languageParam])) {
			$language = $params[$this->languageParam];
			unset($params[$this->languageParam]);
			$languageRequired = true;
		} else {
			$language = Yii::$app->language;
			$languageRequired = false;
		}
		$url = parent::createUrl($params);

		if (!$languageRequired && !$localeUrls->enableDefaultSuffix && $language === $localeUrls->getDefaultLanguage()) {
			return $url;
		} else {
			$key = array_search($language, $localeUrls->languages);
			$base = $this->showScriptName ? $this->getScriptUrl() : $this->getBaseUrl();
			$length = strlen($base);
			if (is_string($key)) {
				$language = $key;
			}
			return $length ? substr_replace($url, "$base/$language", 0, $length) : "/$language$url";
		}
	}
}