<?php
namespace backend\models;

use yii\base\Model;

class BugFilter extends Model
{
	public $version = '';
	public $period = '';
	public $group = 'hash_mini';
	public $app = '';
	public $search = '';

	public function rules()
	{
		return [
			[['version', 'period', 'group', 'app', 'search'], 'safe']
		];
	}
}