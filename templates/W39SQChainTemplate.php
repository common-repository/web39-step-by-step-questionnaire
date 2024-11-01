<?php
declare(strict_types=1);

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQVariable.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQTemplate.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQTemplates.php';

class W39SQChainTemplate extends W39SQTemplate
{
    public function __construct()
    {
        parent::__construct(W39SQTemplates::TEMPLATE_CHAIN, true);

        $this->img = W39SQ_PLUGIN_URL . '/admin/images/chain.jpg';
        $this->name = __('Цепочка', 'w39sq');
        $this->slug = 'chain';

        $this->variables[] = new W39SQVariable('color_point_before', W39SQVariable::VAR_COLOR, '#ffffff', __('Внешний круг ячейки', 'w39sq'));
        $this->variables[] = new W39SQVariable('color_point_after', W39SQVariable::VAR_COLOR, '#afafaf', __('Внутренний круг ячейки', 'w39sq'));

        $this->pagination_item = '<span class="' . W39SQTemplates::CLASS_ITEM . ' w39sq-chain-pagination-item"></span>';

        $css = <<<CSS
/* pagination  ***/
.w39sq-chain-pagination{
    display: flex;
    justify-content: space-between;
    position: relative;
    bottom: 0;
    z-index: -1;
    padding-top: 7px;
    margin-bottom: 30px;
    width: 58.33333333%;
}
.w39sq-chain-pagination-item{
    position: relative;
    height: 12px;
    background: [color_new_line];
    box-shadow: inset 0px 4px 2px rgb(225 225 225 / 25%);
    opacity: 1;
    width: 100%;
    border-radius: 0;
    margin-left: 0;
    margin-right: 0;        
}
.w39sq-chain-pagination-item:before{
    content: "";
    width: 22px;
    height: 22px;
    background: [color_point_before];
    box-shadow: 0px 0px 10px [color_main];
    left: 0;
    top: -5px;
    border-radius: 50%;
    position: absolute;
    display: inline-flex;
}
.w39sq-chain-pagination-item:after{
    content: "";
    width: 12px;
    height: 12px;
    background: [color_point_after];
    border-radius: 50%;
    top: 0;
    left: 5px;        
    position: absolute;
    display: inline-flex;
}
.w39sq-chain-pagination-item.complete{background: [color_main];}
.w39sq-chain-pagination-item.active:after {background: [color_active];}
.w39sq-chain-pagination-item:last-of-type {width: 22px;}
/*** pagination  */

/* кнопки слайдера ***/
.w39sq-swiper-button {
position: relative;
    left: auto;
    right: auto;   
    display: flex;
    align-items: center;
    justify-content: center;
    width: 170px;
    height: 55px;
    border-radius: 10px;
    background: [color_main];
    box-shadow: 0px 0px 10px rgb(207 207 207 / 40%);
}
.w39sq-swiper-button-prev {
    margin-right: 30px;
}
.w39sq-swiper-button-prev:after {
    content: '[caption_button_prev]';
}
.w39sq-swiper-button-next:after {
    content: '[caption_button_next]';
}
.w39sq-swiper-button-prev:after, .w39sq-swiper-button-next:after {
    display: inline-flex;
    /*font-family: 'Gotham Pro';*/
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    line-height: 12px;
    text-align: center;
    text-transform: uppercase;
    color: [color_label]
}
/*** кнопки слайдера */

/* БЛОК ЦЕНЫ */
@media (min-width: 600px) {
    .w39sq-price-block {
        margin: auto;
        margin-left: 40px;
    }
}

.w39sq-price-block-caption {
    margin: auto;
    font-size: [size_step_title]px;
}
.w39sq-price-block-cost{
    margin: auto;
    margin-left: 20px;
    color: [color_main];
    font-size: calc([size_step_title]px + 6px);
    font-weight: 700;
    letter-spacing: 2px;
}
/** Раздел Slider **/


@media (min-width: 600px) {
    .left_block{
        width: 65%;
    }
}

.w39sq-block_info img {
    height: 300px;
    width: 100%;
    border-radius: 10px;
}
/** ** **/

/* МОБИЛЬНАЯ ВЕРСТКА */
@media (max-width: 599px) {
    .w39sq-chain-pagination{
        width: 95%;
    }
    .left_block{
        width: 100%;
    }
}

CSS;

        $this->addStyle($css);

        $this->code = <<<HTML
<div id="w39sq" data-id="{id}" data-count="{count-question}" data-cost="{with-cost}">
<h2 class="w39sq-h2">{title}</h2>
<p class="w39sq-description">{description}</p>
<div id="w39sq_form" data-method="post">
    <input type="text" name="anti_text" value="" class="hidden-input" />
    <input type="checkbox" name="anti_check" class="hidden-input" value="true" checked="checked"/>
    <div class="w39sq-swipper">
        <div class="w39sq-paginator w39sq-chain-pagination">
            {pagination-item}
        </div>                        
        <div class="w39sq-slider">
            {slider-item}        
        </div>       
        <div class="w39sq-navigation">           
            <div class="w39sq-navigation-button">
                <div id="w39sq_prev" class="w39sq-swiper-button-prev w39sq-swiper-button"></div>               
                <div id="w39sq_next" class="w39sq-swiper-button-next w39sq-swiper-button"></div>        
            </div>
            <div class="w39sq-price-block">               
                <div class="w39sq-price-block-caption">{caption-cost}</div>
                <div id="w39sq_cost" class="w39sq-price-block-cost" data-currency="{currency-cost}" data-locale="{locale-cost}">0{currency-cost}</div>
            </div>
        </div>        
    </div>
</div>


HTML;

        $this->slider_item = <<<HTML
<div class="mobile-visible w39sq-block_info">
{info-block}
</div>
<div class="left_block w39sq-block_text">
{question-block}
</div>
<div class="right_block not-mobile w39sq-block_info">
<span class="w39sq-info-text">{info-block}</span>
</div>
HTML;
    }
}