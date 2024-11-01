<?php
declare(strict_types=1);

class W39SQVariable
{
    const VAR_COLOR = 101; //#f0f044
    const VAR_SIZE = 102; //16px
    const VAR_TEXT = 103; //Для подписей

    public string $variable; //Переменная, для замены в тексте, обрамляем []
    public string $value;
    public string $caption;
    public int $type;

    public function __construct($variable = '', $type = self::VAR_COLOR, $value = '', $caption = '')
    {
        $this->variable = $variable;
        $this->type = $type;
        $this->value = $value;
        $this->caption = $caption;
    }

    public function typeInput()
    {
        if ($this->type == self::VAR_COLOR) return 'text';
        if ($this->type == self::VAR_SIZE) return 'number';
        if ($this->type == self::VAR_TEXT) return 'text';
        return 'text';
    }

    public function isFor(string $_var): bool
    {
        return $this->variable == $_var;
    }
}