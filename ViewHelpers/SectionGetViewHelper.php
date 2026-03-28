<?php
namespace ViewHelpers;

class SectionGetViewHelper {
    public function __invoke(string $name) {
        return \Core\View::sectionGet($name);
    }
}
