<?php
use backend\assets\AppAsset;
use backend\widgets\LanguageDropdown;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title>CARAT - <?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => 'CARAT',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-default navbar-fixed-top',
                ],
            ]);
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => Yii::t('app', 'LINK_LOGIN'), 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => Yii::t('app', 'LINK_LOGOUT', ['username' => Yii::$app->user->identity->username]),
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ];
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
            ]);
            NavBar::end();
        ?>

        <div class="container">
        <?= Breadcrumbs::widget([
			'homeLink' => ['label' => Yii::t('app', 'LINK_HOME'), 'url' => '/'],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
        <p class="pull-left">&copy; Gesha <?= date('Y') ?></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
		<?= LanguageDropdown::widget([
			'label' => 'language',
			'containerOptions' => [
				'class' => 'btn-language pull-right',
			]
		]); ?>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
