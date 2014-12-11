<?php

use yii\mongodb\Migration;

class m141121_131442_init extends Migration
{
    public function up()
    {
		$this->createCollection('crash', [
			'capped' => false,
		]);
		$this->createIndex('crash', 'bug');
		$this->createIndex('crash', 'bug_mini');
		$this->createIndex('crash', 'app_version_name');
		$this->createIndex('crash', 'user_crash_date');
		$this->createIndex('crash', 'package_name');
		$this->createIndex('crash', ['full_info' => 'text']);

		/*$this->createCollection('bug', [
			'capped' => false,
		]);
		$this->createIndex('crash', 'count_crashes');
		$this->createIndex('crash', 'last_crashed');

		$this->createCollection('bug_mini', [
			'capped' => false,
		]);
		$this->createIndex('crash', 'count_crashes');
		$this->createIndex('crash', 'last_crashed');*/
    }

    public function down()
    {
        $this->dropCollection('crash');
		/*$this->dropCollection('bug');
		$this->dropCollection('bug_mini');*/
    }
}
