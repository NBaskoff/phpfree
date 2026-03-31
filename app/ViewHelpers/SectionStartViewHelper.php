<?php
namespace ViewHelpers;

class SectionStartViewHelper {
    public function __invoke(string $name) {
        \Core\View::sectionStart($name);
    }
}
