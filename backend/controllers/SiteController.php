<?php
namespace backend\controllers;

use Yii;
use backend\data\MongoArrayDataProvider;
use backend\models\BugFilter;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\data\Sort;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\mongodb\Connection;
use yii\mongodb\Query;
use yii\web\Controller;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use yii\web\Cookie;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'dashboard', 'index', 'bug', 'crash', 'resolve'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

	public function actionIndex()
	{
		$this->redirect(['site/dashboard']);
	}

    public function actionDashboard()
    {
		/* @var $db Connection */
		$db = Yii::$app->mongodb;
		$attributes = Yii::$app->getRequest()->getQueryParam('BugFilter');
		$model = new BugFilter();

		$apps = (new Query())
			->from('crash')
			->distinct('package_name');
		$apps = array_combine($apps, $apps);

		if ($attributes) {
			$model->setAttributes($attributes);
		}
		$cookies = Yii::$app->request->cookies;
		if (!$model->app && $apps) {
			$app = (string)$cookies['app'];
			if ($app && in_array($app, $apps)) {
				$model->app = $app;
			} else {
				foreach ($apps as $app) {
					if ($app) {
						$model->app = $app;
						break;
					}
				}
			}
		}
		Yii::$app->response->cookies->add(new Cookie([
			'name' => 'app',
			'value' => $model->app,
			'expire' => time() + 3600 * 24 * 30
		]));
		$collection = $db->getCollection('crash');
		$pipelines = [];
		$pipelines['match']['$match']['package_name'] = (string)$model->app;
		if ($model->version) {
			$pipelines['match']['$match']['app_version_name'] = $model->version;
		}
		if ($model->info) {
			$pipelines['match']['$match']['info'] = 1;
		} else {
			$pipelines['match']['$match']['info'] = ['$exists' => false];
		}
		if ($model->correctable) {
			$pipelines['match']['$match']['correctable'] = 1;
		}
		$sort = new Sort([
			'attributes' => [
				'id' => ['default' => SORT_DESC],
				'cnt' => ['default' => SORT_DESC],
				'ucd' => ['default' => SORT_DESC]
			],
			'defaultOrder' => ['id' => SORT_DESC]
		]);
		$group = '$hash_mini';
		if ($groupBy = $model->group) {
			$group = $groupBy == 'hash' ? '$hash' : '$hash_mini';
		}

		$to = strtotime(date('Y-m-d'));
		$from = $to - 3600 * 24 * 7;
		if ($model->dateRange) {
			$parts = explode(' - ', $model->dateRange);
			if (count($parts) == 2) {
				$from = strtotime($parts[0]);
				$to = strtotime($parts[1]);
			}
			$pipelines['match']['$match']['user_crash_date'] = ['$gte' => new \MongoDate($from), '$lte' => new \MongoDate($to + 3600 * 24)];
		}
		$pipelinesGraph = $pipelines;
		if ($model->search) {
			$pipelines['match']['$match']['full_info'] = new \MongoRegex("/$model->search/i");
		}
		if ($model->searchNot) {
			$parts = explode(';', $model->searchNot);
			$searchArray = array_map(function($i) { $i = trim($i); return new \MongoRegex("/$i/i"); }, $parts);
			$pipelines['notmatch']['$match']['stack_trace']['$not']['$in'] = $searchArray;
		}
		$pipelines['group'] = [
			'$group' => [
				'_id' => $group,
				'hash' => ['$first' => '$hash'],
				'hash_mini' => ['$first' => '$hash_mini'],
				'stm' => ['$first' => '$stack_trace_mini'],
				'avn' => ['$max' => '$app_version_name'],
				'id' => ['$max' => '$_id'],
				'cnt' => ['$sum' => 1],
				'avc' => ['$max' => '$app_version_code'],
				'ucd' => ['$max' => '$user_crash_date'],
				'res' => ['$max' => '$resolved'],
			]
		];
		if ($model->hideResolved) {
			$pipelines['hide'] = ['$match' => ['res' => 0]];
		}
		$pipelines['count'] = ['$group' => ['_id' => null, 'count' => ['$sum' => 1]]];
		$result = $collection->aggregate(array_values($pipelines));
		unset($pipelines['count']);
		$orders = $sort->getOrders();
		if ($orders) {
			foreach ($orders as $name => $direction) {
				$pipelines['order']['$sort'][$name] = ($direction == SORT_DESC ? -1 : 1);
			}
		}
		$pages = new Pagination([
			'totalCount' => (int)@$result[0]['count'],
			'defaultPageSize' => Yii::$app->params['countPerPage'],
		]);
		$pipelines['offset'] = ['$skip' => $pages->getOffset()];
		$pipelines['limit'] = ['$limit' => $pages->getLimit()];
		$bugs = $collection->aggregate(array_values($pipelines));
		foreach ($bugs as $i => $bug) {
			if (!$bug['res']) {
				$hash = $groupBy == 'hash' ? $bug['hash'] : $bug['hash_mini'];
				$bugs[$i]['res'] = (int)Yii::$app->redis->hget('resolved.bugs', $hash);
			}
		}
		$data = new MongoArrayDataProvider([
			'key' => 'id',
			'allModels' => $bugs,
			'sort' => $sort,
			'pagination' => $pages
		]);

		if (Yii::$app->request->getIsPjax()) {
			return $this->renderPartial('_bugs', ['data' => $data, 'model' => $model]);
		} else {
			$versions = (new Query())
				->from('crash')
				->where(['package_name' => $model->app])
				->distinct('app_version_name');
			$versions = array_combine($versions, $versions);
			foreach ($versions as $i => $v) {
				$versions[$i] = Yii::t('app', 'BUG_FILTER_VERSION').' '.$v;
			}
			uksort($versions, function($first, $second) {
				return version_compare($first, $second, '>');
			});

			$dataGraph = [];
			for ($i = $from; $i <= $to; $i += 3600 * 24) {
				$dataGraph[$i] = array($i * 1000, 0);
			}
			$pipelinesGraph['match']['$match']['user_crash_date'] = ['$gte' => new \MongoDate($from), '$lte' => new \MongoDate($to + 3600 * 24)];
			$pipelinesGraph['graph'] = [
				'$group' => [
					'_id' => [
						'day' => ['$dayOfMonth' => '$user_crash_date'],
						'month' => ['$month' => '$user_crash_date'],
						'year' => ['$year' => '$user_crash_date'],
					],
					'count' => ['$sum' => 1],
				]
			];
			$graph = $collection->aggregate(array_values($pipelinesGraph));
			foreach ($graph as $point) {
				$date = strtotime($point['_id']['year'].'-'.$point['_id']['month'].'-'.$point['_id']['day']);
				$dataGraph[$date] = array($date * 1000, $point['count']);
			}
			ksort($dataGraph);
			$series[] = array(
				'data' => array_values($dataGraph),
				'type' => 'spline',
				'shadow' => true,
				'marker' => array(
					'enabled' => true,
					'radius' => 3
				)
			);

        	return $this->render('dashboard', ['versions' => $versions, 'apps' => $apps, 'data' => $data, 'model' => $model, 'series' => $series]);
		}
    }

	public function actionResolve()
	{
		/* @var $db Connection */
		$db = Yii::$app->mongodb;
		$redis = Yii::$app->redis;
		$attribute = Yii::$app->request->post('attribute');
		$hash = Yii::$app->request->post('hash');
		$version = (int)Yii::$app->request->post('version');

		$collection = $db->getCollection('crash');
		if ($version) {
			$versions = $collection->distinct('app_version_code', [$attribute => $hash]);
			foreach ($versions as $v) {
				if ($v > $version) {
					$version = $v;
				}
			}
		}
		$collection->update([$attribute => $hash], ['resolved' => $version]);
		if ($version) {
			$redis->hset('resolved.bugs', $hash, $version);
		} else {
			$redis->hdel('resolved.bugs', $hash);
		}
	}

	public function actionBug($hash, $useful)
	{
		/* @var $db Connection */
		$db = Yii::$app->mongodb;

		$collection = $db->getCollection('crash');
		$field = $useful ? 'hash_mini' : 'hash';

		$count = $collection->find([
			$field => $hash
		])->count();
		$pages = new Pagination([
			'totalCount' => $count,
			'defaultPageSize' => Yii::$app->params['countPerPage'],
		]);
		$crashes = $collection->find([
			$field => $hash
		])->sort(['_id' => -1])->skip($pages->getOffset())->limit($pages->getLimit());
		$models = [];
		$iOs = false;
		foreach ($crashes as $crash) {
			$fullInfo = Json::decode($crash['full_info']);
			$fullInfo['resolved'] = (int)@$crash['resolved'];
			$fullInfo['id'] = (string)$crash['_id'];
			$fullInfo['st'] = $crash['stack_trace'];
			if (isset($crash['device_id'])) {
				$iOs = true;
				$fullInfo['pn'] = str_replace(' (iPhone)', '', $crash['package_name']);
			}
			$models[] = $fullInfo;
		}
		$data = new MongoArrayDataProvider([
			'allModels' => $models,
			'pagination' => $pages,
			'sort' => false,
		]);

		if ($iOs) {
			if (Yii::$app->request->getIsPjax()) {
				return $this->render('_crashes_ios', ['data' => $data]);
			} else {
				return $this->render('bug_ios', ['data' => $data]);
			}
		} else {
			if (Yii::$app->request->getIsPjax()) {
				return $this->render('_crashes', ['data' => $data]);
			} else {
				return $this->render('bug', ['data' => $data]);
			}
		}
	}

	public function actionCrash($id)
	{
		/* @var $db Connection */
		$db = Yii::$app->mongodb;

		$collection = $db->getCollection('crash');
		$crash = $collection->findOne([
			'_id' => $id
		]);
		$data = array();
		if ($crash) {
			$data = Json::decode($crash['full_info']);
		}
		foreach ($data as $key => $value) {
			$data[$key] = str_replace(array("\n", "\t"), array('<br>', '&nbsp;&nbsp;&nbsp;&nbsp;'), $value);
		}

		return $this->render('crash', ['crash' => $data]);
	}

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
