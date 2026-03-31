<?php

namespace Controllers;

use Exception;

class IndexController extends BaseController
{
    /**
     * @throws Exception
     */
    public function actionIndexGet(): string
    {
        $userData = [
            'title'    => 'Главная страница сайта',
            'userName' => 'Александр',
            'userId'   => 777
        ];
        return $this->display('main', $userData);
    }
}