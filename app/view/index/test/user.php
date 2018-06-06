<ul>
    <li>姓名： <?= $user['name'] ?></li>
    <li>年龄： <?= $user['age'] ?></li>
    <li>头像：
    <?php foreach ($user['avators'] as $k => $v): ?>
        <p>
            <img src="<?= $v ?>">
        </p>
    <?php endforeach; ?>
    </li>
</ul>