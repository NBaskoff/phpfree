<?php

namespace Controllers;

use Core\View;

class IndexController
{
    /**
     * @throws \Exception
     */
    public function actionIndexGet()
    {
        echo View::render('main', [
            'title'    => 'Главная страница сайта',
            'userName' => 'Александр',
            'userId'   => 777
        ]);
    }
}