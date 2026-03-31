<?php
namespace ViewHelpers;

class LayoutViewHelper {
    public function __invoke(string $name) {
        \Core\View::layout($name);
    }
}
