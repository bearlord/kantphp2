<?php if ($this->result): ?>
    <ul>
        <li><?php echo $this->result['id'] ?> => <?php echo $this->result['name'] ?></li>
    </ul>
<?php else: ?>
    暂无数据
<?php endif; ?>