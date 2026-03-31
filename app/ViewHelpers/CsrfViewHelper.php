<?php

namespace ViewHelpers;

use Core\Contract;
use Contracts\SessionContract;

class CsrfViewHelper
{
    public function __invoke(): string
    {
        $session = Contract::make(SessionContract::class);

        if (!$session->has('_csrf')) {
            $session->set('_csrf', bin2hex(random_bytes(32)));
        }

        $token = $session->get('_csrf');
        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
    }
}
