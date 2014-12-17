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
    }

    public function down()
    {
        $this->dropCollection('crash');
    }
}
