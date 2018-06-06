<?php
    use app\module\index\Assets\AppAsset;
    AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
    <head>
        <title>layout</title>
        <?php $this->head() ?>
    </head>
    <?php $this->beginBody() ?>
    <body>
        <header></header>
        <main>
            <div class="wrap">
                <?php
                echo $content;
                ?>
            </div>

        </main>  
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage(true) ?>
