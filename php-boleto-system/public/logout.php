<?php

declare(strict_types=1);

require __DIR__ . '/../app/auth.php';

logoutUser();
redirect('/index.php');
