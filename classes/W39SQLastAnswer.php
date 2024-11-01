<?php
declare(strict_types=1);

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAdmin.php';


class W39SQLastAnswer
{
    public bool $name = true;
    public bool $phone = true;
    public bool $email = true;
    public bool $other = false;
    public string $other_text = '';
    public string $caption_submit;
    public string $info = '';
    public bool $info_img = true; //false - текст, true - картинка в info
    public string $mask_phone = '+7(999) 999 9999';
    public string $title = '';

    public function __construct()
    {
        $this->caption_submit = __('Отправить', 'w39sq');
    }

    public function generateSubmit(): string
    {
        $count = ($this->name ? 1 : 0) + ($this->phone ? 1 : 0) + ($this->email ? 1 : 0) + ($this->other ? 1 : 0) + 1;

        $class = [];
        switch ($count) {
            case 2:
                $class = [0 => 'w39sq-col-2', 1 => 'w39sq-col-2'];
                break;
            case 3:
                $class = [0 => 'w39sq-col-3', 1 => 'w39sq-col-3', 2 => 'w39sq-col-3'];
                break;
            case 4:
                $class = [0 => 'w39sq-col-1', 1 => 'w39sq-col-3', 2 => 'w39sq-col-3', 3 => 'w39sq-col-3'];
                break;
            case 5:
                $class = [0 => 'w39sq-col-2', 1 => 'w39sq-col-2', 2 => 'w39sq-col-3', 3 => 'w39sq-col-3', 4 => 'w39sq-col-3'];
                break;
        }

        $item = [];
        if ($this->name) $item[] = '<input type="text" name="user_name" class="w39sq-field-text required" placeholder="' .
            __('Имя', 'w39sq') .
            '"/>';
         if ($this->phone)
             $item[] = '<input id="user_phone" type="text" name="user_phone" class="w39sq-field-text required" placeholder="' .
                 __('Телефон', 'w39sq') .
                 '" data-mask="' . esc_html($this->mask_phone) . '"/>';
         if ($this->email)
             $item[] = '<input type="email" name="user_email" class="w39sq-field-text required" placeholder="' . __('Электронная почта', 'w39sq') . '"/>';
         if ($this->other)
             $item[] = '<input type="text" name="user_other" class="w39sq-field-text" placeholder="' . esc_html($this->other_text) . '"/>';

        $item[] = '<button id="w39sq-submit" type="button" class="w39sq-submit" />' . esc_html($this->caption_submit) . '</button>';

        ob_start() ?>
        <p class="step-title"><?php echo esc_html($this->title) ?></p>
        <div class="w39sq-row">
            <?php for($i = 0; $i < $count; $i++): ?>
                <div class="<?php echo esc_html($class[$i]) ?>">
                    <?php echo wp_kses($item[$i], W39SQAdmin::FILTER_INPUT) ?>
                    <span class="error-span-text"></span>
                </div>
            <?php endfor; ?>
        </div>
        <div id="w39sq-error-block" style="color: red;"></div>
        <?php
        return ob_get_clean();
    }

    public function generateInfo(): string
    {
        if ($this->info_img) {
            $code = '<img class="w39sq-question-img" src="' . $this->info . '">';
        } else {
            $code = '<p class="w39sq-question-text">' . $this->info . '</p>';
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
}