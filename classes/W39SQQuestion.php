<?php
declare(strict_types=1);
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAnswer.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAdmin.php';

class W39SQQuestion
{
    const BLOCK_QUESTION = '{question_block}';
    const BLOCK_INFO = '{info_block}';
    const ANSWER_CHECKBOX = 0;
    const ANSWER_RADIOBUTTON = 1;
    //const ANSWER_TEXT = 2;

    public string $question = '';
    public bool $multi = false; //false - radio, true - checkbox
    public string $info = '';
    public bool $info_img = true; //false - текст, true - картинка в info
    /** @var W39SQAnswer[]  */
    public array $answers = [];

    private const ARRAY_TYPES = [
        self::ANSWER_CHECKBOX => 'checkbox',
        self::ANSWER_RADIOBUTTON => 'radio',
        //self::ANSWER_TEXT => 'text',
    ];

    public static function create($question, $multi, $info, $info_img = true, array $answers = []): self
    {
        $quest = new static();
        $quest->question = $question;
        $quest->multi = $multi;
        $quest->info = $info;
        $quest->info_img = $info_img;
        $quest->answers = $answers;
        return $quest;
    }

    public function addAnswer(W39SQAnswer $answer)
    {
        $this->answers[] = $answer;
    }

    public function generateQuestion(int $num_question, int $count_question, bool $with_cost): string
    {
        //TODO
        ob_start() ?>
        <input type="hidden" name="question" data-multi="<?php echo esc_html($this->multi) ?>" value="<?php echo esc_html($this->question)?>">
        <p class="step-count"><?php echo sprintf(__('Вопрос %d из %d', 'w39sq'), esc_html($num_question + 1), esc_html($count_question)) ?></p>
        <p class="step-title"><?php echo esc_html($this->question) ?></p>
        <?php foreach ($this->answers as $num => $answer): ?>
        <?php
            $_name = '';
            $id_field = 'w39sq-field-' . $num_question . '-' . $num;
            if ($this->multi) $_name = '[' . $num . ']';
            ?>
        <div class="w39sq-question-field-group">
            <input id="<?php echo esc_html($id_field) ?>" class="w39sq-field" type="<?php echo esc_html($this->type_input()) ?>"
                   name="question[<?php echo esc_html($num_question) ?>]<?php echo esc_html($_name) ?>" value="<?php echo esc_html($answer->text) ?>"
                <?php if ($with_cost) echo 'data-mincost="' . esc_html($answer->min_cost) . '" data-maxcost="'. esc_html($answer->max_cost) .'"'; ?> >
            <label for="<?php echo esc_html($id_field) ?>"><?php echo esc_html($answer->text) ?></label>
        </div>
        <?php endforeach; ?>

<?php
        return ob_get_clean();
    }

    public function generateInfo(): string
    {
        if ($this->info_img) {
            $code = '<img class="w39sq-question-img" src="' . esc_url($this->info) .'">';
        } else {
            $code = '<div class="w39sq-question-text">' . wp_kses($this->info, W39SQAdmin::FILTER) .'</div>';
        }
        return $code;

    }

    public function getInfo(bool $isImage = true): string
    {
        if ($isImage) {
            if (strpos($this->info, '://') == false) {
                return '';
            } else {
                return $this->info;
            }
        } else {
            if (strpos($this->info, '://') == false) {
                return $this->info;
            } else {
                return '';
            }
        }
    }

    private function type_input(): string
    {
        return $this->multi ? 'checkbox' : 'radio';
    }

}