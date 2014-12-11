<?php
namespace backend\data;

use yii\data\ArrayDataProvider;

class MongoArrayDataProvider extends ArrayDataProvider
{
	/**
	 * @inheritdoc
	 */
	protected function prepareModels()
	{
		return $this->allModels;
	}

	/**
	 * @inheritdoc
	 */
	protected function prepareKeys($models)
	{
		if ($this->key !== null) {
			$keys = [];
			foreach ($models as $model) {
				if (is_string($this->key)) {
					$keys[] = $model[$this->key];
				} else {
					$keys[] = call_user_func($this->key, $model);
				}
			}

			return $keys;
		} else {
			return array_keys($models);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function prepareTotalCount()
	{
		return $this->pagination->totalCount;
	}

	/**
	 * @inheritdoc
	 */
	protected function sortModels($models, $sort)
	{
		return $models;
	}
}
