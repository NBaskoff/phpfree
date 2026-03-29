<?php
return [
    // В шаблоне будет vh_layout(), но вызовет он LayoutViewHelper
    'layout' => \ViewHelpers\LayoutViewHelper::class,
    'section_start' => \ViewHelpers\SectionStartViewHelper::class,
    'section_end' => \ViewHelpers\SectionEndViewHelper::class,
    'section_get' => \ViewHelpers\SectionGetViewHelper::class,
    'mix' => \ViewHelpers\MixViewHelper::class,
    'csrf' => \ViewHelpers\CsrfViewHelper::class,
    // Пример кастомного хелпера
    // 'format_price' => \ViewHelpers\FormatPriceHelper::class,
];