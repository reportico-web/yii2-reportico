<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

//$asset = reportico\reportico\ReporticoAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<div class="container">
    <?= $content ?>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">A Product of <a href="http://www.reportico.org/">Reportico</a></p>
        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
