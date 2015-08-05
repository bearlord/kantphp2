<?php $this->includeTpl("index/displayfunc"); ?>
<ul>
    <li>String:<?php echo $this->str; ?></li>
    <?php foreach ($this->row as $key => $value) : ?>
        <li><?php echo $key; ?>=><?php echo $value; ?></li>
    <?php endforeach; ?>
</ul>

