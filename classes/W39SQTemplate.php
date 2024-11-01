<?php
declare(strict_types=1);



class W39SQTemplate
{
    //Общие параметры
    private int $id;
    private bool $free = true;
    public string $name;
    public string $img;

    public string $slug;

    //стили css и переменные для замены
    /** @var W39SQVariable[] */
    public array $variables = [];
    private string $style = '';
    public string $code = '';
    public string $pagination_item = '';
    public string $slider_item = '';

    //блоки замены в шаблоне - для справки
    const BLOCK_ID = '{id}';
    const BLOCK_TITLE = '{title}';
    const BLOCK_DESCRIPTION = '{description}';
    const BLOCK_COUNT_QUESTION = '{count-question}';
    const BLOCK_PAGINATION_ITEM = '{pagination-item}';
    const BLOCK_WITH_COST = '{with-cost}';
    const BLOCK_SLIDER_ITEM = '{slider-item}';
    const BLOCK_CAPTION_COST = '{caption-cost}';
    const BLOCK_CURRENCY_COST = '{currency-cost}';
    const BLOCK_LOCALE_COST = '{locale-cost}';
    const BLOCK_QUESTION_BLOCK = '{question-block}';
    const BLOCK_INFO_BLOCK = '{info-block}';

    public function __construct(int $template, bool $free = true)
    {
        $this->id = $template;
        $this->free = $free;

        $this->setBaseVariables();
        $this->setBaseStyle();
    }

    public function isFor(int $id): bool
    {
        return $this->id == $id;
    }

    public function isFree(): bool
    {
        return $this->free;
    }

    public function getTemplate(): int
    {
        return $this->id;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function addStyle(string $style)
    {
        $this->style .= $style;
    }

    private function setBaseStyle()
    {
        $css = <<<CSS
.w39sq-description {
    font-size: [size_description]px !important;
    font-weight: 600;
}

/* Поля ввода */
#w39sq input {
 border: 2px solid [color_main] !important;
}
#w39sq input[type=checkbox]:checked:before {
 color: [color_active] !important;
}
#w39sq input[type=radio]:checked:before {
   background-color: [color_active] !important;
}
#w39sq input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus {
    background-image: none;
}
#w39sq input[type="text"], input[type="email"], input[type="tel"] {
    border-radius: 10px;
}
#w39sq button[type=submit] {
    background-color: [color_main] !important;
    border-color: [color_main] !important;
    border-radius: 10px;
    text-transform: uppercase;
}


.step-count {
    color: [color_active];
    font-size: [size_step_count]px !important;
}
.step-title {
    font-size: [size_step_title]px !important;
}

.w39sq-info-text, .w39sq-info-text p{
    font-size: [size_step_info]px !important;
}

.w39sq-question-field-group label, .w39sq-submit {
    font-size: [size_step_label]px !important;
}
/*Кнопка Отправить */
.w39sq-submit {
    background-color: [color_main];
    color: [color_label];
    text-transform: uppercase; 
    border-color: [color_main];
    webkit-transition: all .3s cubic-bezier(.645,.045,.355,1);
    transition: all .3s cubic-bezier(.645,.045,.355,1);
}
.w39sq-submit:active, .w39sq-submit:focus {
    background-color: [color_main];
    border-color: [color_main];
}
.w39sq-submit:hover {    
    background-color: [color_main];
    border-color: [color_main];
    transform: scale(1.1);
    webkit-transition: all .3s cubic-bezier(.645,.045,.355,1);
    transition: all .3s cubic-bezier(.645,.045,.355,1);
}

#w39sq input[type=text], #w39sq input[type=email] {
    font-size: [size_step_label]px !important;
}


.w39sq-response {
    background-color: [color_main];
}
.contact-response, .title-response, .description-response {
 color: [color_label];
}
.contact-response a:hover {
 color: [color_new_line];
}

CSS;

        $this->style = $css;
    }

    private function setBaseVariables()
    {
        //Создаем базовые переменные для всех шаблонов

        $this->variables[] = new W39SQVariable('color_main', W39SQVariable::VAR_COLOR, '#8106df', __('Главный цвет', 'w39sq'));
        $this->variables[] = new W39SQVariable('color_active', W39SQVariable::VAR_COLOR, '#71000f', __('Активный цвет', 'w39sq'));
        $this->variables[] = new W39SQVariable('color_new_line', W39SQVariable::VAR_COLOR, '#333333', __('Вторичный цвет', 'w39sq'));

        $this->variables[] = new W39SQVariable('color_label', W39SQVariable::VAR_COLOR, '#000000', __('Надпись на кнопках', 'w39sq'));

        $this->variables[] = new W39SQVariable('size_description', W39SQVariable::VAR_SIZE, '20', __('Размер описания (px)', 'w39sq'));
        $this->variables[] = new W39SQVariable('size_step_count', W39SQVariable::VAR_SIZE, '18', __('Размер Шага (px)', 'w39sq'));
        $this->variables[] = new W39SQVariable('size_step_title', W39SQVariable::VAR_SIZE, '24', __('Размер Вопроса (px)', 'w39sq'));
        $this->variables[] = new W39SQVariable('size_step_label', W39SQVariable::VAR_SIZE, '16', __('Размер Ответа (px)', 'w39sq'));
        $this->variables[] = new W39SQVariable('size_step_info', W39SQVariable::VAR_SIZE, '20', __('Размер Инфо блока (px)', 'w39sq'));

        $this->variables[] = new W39SQVariable('caption_button_prev', W39SQVariable::VAR_TEXT, __('назад', 'w39sq'), __('Кнопка Назад', 'w39sq'));
        $this->variables[] = new W39SQVariable('caption_button_next', W39SQVariable::VAR_TEXT, __('вперед', 'w39sq'), __('Кнопка Вперед', 'w39sq'));
    }
}