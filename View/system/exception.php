<?php
use Kant\Kant;
use Kant\View\ExceptionAsset;

ExceptionAsset::register($this);
?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<title>Kantphp Framework Application Exception</title>
<style type="text/css">
.footer {
	position: absolute;
	bottom: 0;
	width: 100%;
	height: 60px;
	background-color: #f5f5f5;
}
</style>
        <?php $this->head()?>
    </head>
<body>
        <?php $this->beginBody()?>
        <div class="container">
		<div class="page-header">
			<h1><?= strip_tags($error['message']); ?></h1>
		</div>
            <?php if (isset($error['file'])): ?>
                <h3>Line</h3>
		<p>FILE: <?= $error['file']; ?> &#12288;LINE: <?= $error['line']; ?></p>
            <?php endif; ?>
            <?php if (isset($error['trace'])): ?>
                <h3>TRACE</h3>
		<p><?= nl2br($error['trace']); ?></p>
            <?php endif; ?>
        </div>

	<footer class="footer">
		<div class="container">
			<p>
				<a title="Kantphp Framework" href="http://www.kantphp.com">Kantphp Framework <?= Kant::getVersion(); ?></a>
			</p>
		</div>
	</footer>
</body>
    <?php $this->endBody()?>
</html>
<?php $this->endPage(true)?>
