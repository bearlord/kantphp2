
<ul>
    <li>总数：<?php echo $this->result[1] ?></li>
    <?php foreach ($this->result[0] as $key => $value) : ?>
        <li><?php echo $value['id']; ?>=><?php echo $value['name']; ?></li>
    <?php endforeach; ?>
</ul>

