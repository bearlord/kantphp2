<?php

return [
    '[blog]' => [
        ':id' => ['Blog/read', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['Blog/read', ['method' => 'post']],
    ],
];

