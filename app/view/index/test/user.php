<table class="table">
    <thead>
        <tr>
            <th>姓名</th>
            <th>年龄</th>
            <th>头像</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $k => $user): ?>
            <tr>
                <td><?= $user['name'] ?></td>
                <td><?= $user['age'] ?></td>
                <td>
                    <div class="row">
                        <?php foreach ($user['avators'] as $ka => $va): ?>
                            <div class="col-sm-2">
                                <img src="<?= $va ?>" height="100">
                            </div>
                        <?php endforeach; ?>
                    </div>

                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>