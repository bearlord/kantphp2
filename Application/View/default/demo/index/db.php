<ul>
    <?php foreach ($this->row as $key => $val) : ?>
        <li><?php echo $val['uid']; ?>:<?php echo $val['username']; ?></li>
    <?php endforeach; ?>
</ul>

