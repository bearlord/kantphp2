<?php if ($this->result): ?>
    <div >
        <ul>
            <?php foreach ($this->result as $key => $val): ?>
                <li><?php echo $val['id'] ?> =><?php echo $val['name'] ?></li>
            <?php endforeach; ?>

        </ul>
    </div>
    <div >
        <?php echo $this->pages; ?>
    </div>
<?php endif; ?>
