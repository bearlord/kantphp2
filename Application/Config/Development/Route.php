<?php

return [
    'new/:id'   => 'News/read',
    'blog/:id'   => ['Blog/update',['method' => 'post|put'], ['id' => '\d+']],
];

