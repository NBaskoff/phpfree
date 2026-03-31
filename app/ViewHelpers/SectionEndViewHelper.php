<?php
namespace ViewHelpers;

class SectionEndViewHelper {
    public function __invoke() {
        \Core\View::sectionEnd();
    }
}
