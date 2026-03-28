<?php

namespace Controllers;

use Exception;

class IndexController extends BaseController
{
    /**
     * @throws Exception
     */
    public function actionIndexGet()
    {
        $userData = [
            'title'    => 'Главная страница сайта',
            'userName' => 'Александр',
            'userId'   => 777
        ];

        // Вызываем метод родителя
        $this->display('main', $userData);
    }
}