<?php
namespace backend\models;

use yii\base\Model;

class BugFilter extends Model
{
	public $info = 0;
	public $version = '';
	public $period = '';
	public $group = 'hash_mini';
	public $app = '';
	public $search = '';
	public $searchNot = '';

	public function rules()
	{
		return [
			[['info', 'version', 'period', 'group', 'app', 'search', 'searchNot'], 'safe']
		];
	}
}