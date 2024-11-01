<?php
declare(strict_types=1);

//require_once W39SQ_PLUGIN_DIR . '/classes/W39SQ.php';
use JetBrains\PhpStorm\Pure;

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQVariable.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQTemplate.php';
require_once W39SQ_PLUGIN_DIR . '/templates/W39SQChainTemplate.php';

class W39SQTemplates
{
    public array $list;

    //** Основные классы для управления из JS */
    ///* Классы для пагинатора
    const CLASS_PAGINATION = 'w39sq-paginator';
    const CLASS_ITEM = 'w39sq-paginator-item';
    const CLASS_ITEM_COMPLETE = 'complete';
    const CLASS_ITEM_ACTIVE = 'active';
    ///* Классы для шаблона

    //*** Коды Шаблонов ***//
    const TEMPLATE_CHAIN = 7001;
    const TEMPLATE_DIGITAL = 9001;
    const TEMPLATE_UNIVERSITY = 9002;

    /**
     * @return W39SQTemplate[]
     */
    public static function init(): array
    { //Грузим шаблоны из классов
        $list = [];
        $list[] = new W39SQChainTemplate();
        do_action('w39sq_init_templates');
        return $list;
    }

    public static function get(int $template)
    {
        $list = self::init();
        foreach ($list as $item) {
            if ($item->isFor($template)) return $item;
        }
        throw new \DomainException('Шаблон не найден');
    }
}