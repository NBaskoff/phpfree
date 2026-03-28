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
            'userId'   => 7771
        ];
        return $this->display('main', $userData);
    }
}