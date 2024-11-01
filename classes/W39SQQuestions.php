<?php
declare(strict_types=1);
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQQuestion.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQLastAnswer.php';

class W39SQQuestions
{
    /** @var W39SQQuestion[]  */
    public array $questions = [];
    public bool $withCost = false;
    public bool $withInfo = true;
    public string $currency = '$';
    public W39SQLastAnswer $last;

    public function __construct()
    {
        $this->last = new W39SQLastAnswer();
    }

    public function addQuestion(W39SQQuestion $question)
    {
        $this->questions[] = $question;
    }

    public function getAll(): array
    {
        return $this->questions;
    }

    public function count(): int
    {
        return count($this->questions);
    }

    public function clear()
    {
        $this->questions = [];
    }

    public function generateCode(string $slider_item): string
    {
        $result = '';
        foreach ($this->questions as $num => $question) {
            $_slider = $slider_item;
            $_slider = str_replace('{question-block}', $question->generateQuestion($num, $this->count(), $this->withCost), $_slider);
            if ($this->withInfo) {
                $_slider = str_replace('{info-block}', $question->generateInfo(), $_slider);
            } else {
                $_slider = str_replace('{info-block}', '', $_slider);
            }
            $result .= '<div id="slider' . $num . '" class="w39sq-slider-item" data-slider="' . $num . '">' . $_slider . '</div>';
        }
        //Генерация последнего экрана
        $_last_screen = $slider_item;
        $_last_screen = str_replace('{question-block}', $this->last->generateSubmit(), $_last_screen);
        if ($this->withInfo) {
            $_last_screen = str_replace('{info-block}', $this->last->generateInfo(), $_last_screen);
        } else {
            $_last_screen = str_replace('{info-block}', '', $_last_screen);
        }
        $result .= '<div class="w39sq-slider-item">' . $_last_screen . '</div>';

        return $result;
    }
}