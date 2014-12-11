<?php
namespace backend\widgets;

use yii\widgets\DetailView;

class CrashDetailView extends DetailView
{
	protected function renderAttribute($attribute, $index)
	{
		if (is_string($this->template)) {
			return strtr($this->template, [
				'{label}' => $attribute['label'],
				'{value}' => $this->formatter->format($attribute['value'], 'html'),
			]);
		} else {
			return call_user_func($this->template, $attribute, $index, $this);
		}
	}
}