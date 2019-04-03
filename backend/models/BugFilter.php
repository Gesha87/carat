<?php
namespace backend\models;

use yii\base\Model;

class BugFilter extends Model
{
	public $info = 0;
	public $version = '';
	public $code = '';
	public $period = '';
	public $group = 'hash_mini';
	public $app = '';
	public $search = '';
	public $searchNot = '';
	public $correctable = 1;
	public $dateRange;
	public $hideResolved = 1;

	public function rules()
	{
		return [
			[['info', 'version', 'period', 'group', 'app', 'search', 'searchNot', 'correctable', 'dateRange', 'hideResolved', 'code'], 'safe']
		];
	}

	public function getPipelines()
	{
		$pipelines = [];
		$pipelines['match']['$match']['package_name'] = (string)$this->app;
		if ($this->version) {
			$pipelines['match']['$match']['app_version_name'] = $this->version;
		}
		if ($this->code) {
			$pipelines['match']['$match']['app_version_code'] = (int)$this->code;
		}
		if ($this->info) {
			$pipelines['match']['$match']['info'] = (int)$this->info;
		} else {
			$pipelines['match']['$match']['info'] = ['$exists' => false];
		}
		if ($this->correctable) {
			$pipelines['match']['$match']['correctable'] = 1;
		}
		$to = strtotime(date('Y-m-d'));
		$from = $to - 3600 * 24 * 7;
		if ($this->dateRange) {
			$parts = explode(' - ', $this->dateRange);
			if (count($parts) == 2) {
				$from = strtotime($parts[0]);
				$to = strtotime($parts[1]);
			}
			$pipelines['match']['$match']['user_crash_date'] = ['$gte' => new \MongoDB\BSON\UTCDateTime($from * 1000), '$lte' => new \MongoDB\BSON\UTCDateTime(($to + 3600 * 24) * 1000)];
		}
		$pipelinesGraph = $pipelines;
		if ($this->search) {
			$pipelines['match']['$match']['full_info'] = new \MongoDB\BSON\Regex("/$this->search/i");
		}
		if ($this->searchNot) {
			$parts = explode(';', $this->searchNot);
			$searchArray = array_map(function($i) { $i = trim($i); return new \MongoDB\BSON\Regex("/$i/i"); }, $parts);
			$pipelines['notmatch']['$match']['stack_trace']['$not']['$in'] = $searchArray;
		}

		return [$pipelines, $pipelinesGraph, $from, $to];
	}
}