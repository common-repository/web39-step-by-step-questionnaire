<?php
declare(strict_types=1);

class W39SQAdmin
{
    const LINK_MAIN = 'https://website39.site/';
    const LINK_DOC = 'https://website39.site/';
    const LINK_FAQ = 'https://website39.site/';

    const EMAIL_AUTHOR = 'website39.site@gmail.com';
    const CARD_AUTHOR = '4274 3200 6441 0734';
    //общие атрибуты
    private const _tags = [
        'id' => [],
        'style' => [],
        'class' => [],
        'for' => [],
        'href' => [],
        'src' => [],
        'alt' => [],
        'title' => [],
        'rows' => [],
        /* data- */
        'data-count' => [],
        'data-id' => [],
        'data-cost' => [],
        'data-method' => [],
        'data-slider' => [],
        'data-multi' => [],
        'data-currency' => [],
        'data-locale' => [],
        'data-locale-cost' => [],
        'data-current' => [],
        'data-num' => [],
        'data-question' => [],
        'data-block' => [],
        'data-mincost' => [],
        'data-maxcost' => [],
        'data-mask' => [],
        /* input/button */
        'checked' => [],
        'readonly' => [],
        'disabled' => [],
        'type' => [],
        'name' => [],
        'placeholder' => [],
        'onclick' => [],
        'value' => [],
        'min' => [],
        'max'=> [],
    ];
    const FILTER = [
        'b' => self::_tags,
        'i' => self::_tags,
        'em' => self::_tags,
        'strong' => self::_tags,
        'p' => self::_tags,
        'br' => self::_tags,
        'span' => self::_tags,
        'div' => self::_tags,
        'label' => self::_tags,
        'code' => self::_tags,
        'textarea' => self::_tags,
    ];
    const FILTER_PLUGIN = [
        'style' => [],
        'h2' => self::_tags,
        'h3' => self::_tags,
        'h4' => self::_tags,

    ];
    const FILTER_URL = [
        'a' => self::_tags,
        'img' => self::_tags,
    ];
    const FILTER_INPUT = [
        'input' => self::_tags,
        'button' => self::_tags,
    ];
}