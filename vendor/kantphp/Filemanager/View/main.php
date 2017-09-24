<?php

use Kant\Filemanager\FilemanagerAsset;
use Kant\Helper\Html;

FilemanagerAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?></title>
    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
</head>

<?php $this->beginBody() ?>
<body>

<?php echo $content; ?>
<?php $this->endBody() ?>
<script type="text/javascript">
    $(function () {
        $("[data-toggle='tooltip']").tooltip({
            'placement': 'bottom'
        });
    });
</script>
</body>
</html>
<?php $this->endPage(true) ?>
