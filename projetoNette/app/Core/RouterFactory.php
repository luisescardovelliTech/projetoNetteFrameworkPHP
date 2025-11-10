<?php

declare(strict_types=1);

namespace App\Core;

use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
    use Nette\StaticClass;

public static function createRouter(): RouteList
    {
        $router = new RouteList;

        $router->addRoute('posts/upload', 'Posts:upload');

        $router->addRoute('<presenter autor|categoria|posts|tags>[/<id \d+>]', [
            'action' => 'default',
        ]);

        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
    }
}
