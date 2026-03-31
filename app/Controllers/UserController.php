<?php

namespace Controllers;

use Actions\User\ListUsersAction;

class UserController extends BaseController
{
    public function actionIndexGet(ListUsersAction $action): string
    {
        return $this->display('user/index', $action());
    }

    public function actionOneGet($id)
    {
        echo $id;
    }
}

