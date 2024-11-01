<?php
declare(strict_types=1);

class W39SQAnswer
{
    public string $text = '';
    public int $min_cost = 0;
    public int $max_cost = 0;


    public static function create($text, $min = 0, $max = 0): self
    {
        $answer = new static();
        $answer->text = $text;
        $answer->min_cost = $min;
        $answer->max_cost = $max;
        return $answer;
    }


}