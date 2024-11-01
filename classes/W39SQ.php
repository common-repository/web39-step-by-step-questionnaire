<?php
declare(strict_types=1);

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQTemplates.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQQuestions.php';
require_once W39SQ_PLUGIN_DIR . '/classes/W39SQResponse.php';
require_once W39SQ_PLUGIN_DIR . '/classes/JsonMapper.php';

class W39SQ
{
    public string $name = ''; //*
    public string $title = ''; //*
    public string $description = ''; //*
    public string $email = ''; //*
    public string $email_from = ''; //TODO возможно сделать при создании w39sq@domain
    public string $email_subject = ''; //*

    public W39SQQuestions $question;  //*
    public W39SQResponse $response;

    private int $template;

    public string $user_style = '';  //*
    private string $style = '';  //*
    /** @var W39SQVariable[] */
    public array $variables = []; //['variable => '', 'value' => '']

    private string $code = '';

    public string $other = '';  //На будущее

    private ?int $id = null;  //*
    private int $count = 0;  //*не используется

    private int $created_at; //*
    private ?int $updated_at; //*

    private function __construct(){}

    //Создаем объект из шаблона
    public static function create(int $template, string $name): self
    {
        $w = new static();
        $w->template = $template;
        $w->name = $name;
        $w->question = new W39SQQuestions();
        $w->response = new W39SQResponse();
        $w->created_at = time();
        //Загружаем шаблон
        $w->reloadVariables();
        $w->code = $w->generateCode();
        $domain =preg_replace("(^https?://)", '', get_site_url());
        $w->email_from = 'w39sq@' . $domain;
        return $w;
    }

    public static function load(int $id): self //Загрузка из БД
    {
        $w = new static();
        $w->_load($id);
        return $w;
    }

    public function Id(): ?int
    {
        return $this->id;
    }

    public function save()
    {
        try {
            global $wpdb;
            $data = [
                'name' => $this->name,
                'title' => $this->title,
                'email' => $this->email,
                'email_from' => $this->email_from,
                'email_subject' => $this->email_subject,
                'template' => $this->template,
                'style' => $this->style,
                'user_style' => $this->user_style,
                'code' => $this->generateCode(),
                'question' => json_encode($this->question),
                'variables' => json_encode($this->variables),
                'other' => $this->other,
                'count' => $this->question->count(),
                'created_at' => $this->created_at,
                'description' => $this->description,
                'response' => json_encode($this->response),
            ];
            if (!isset($this->id)) {
                $wpdb->insert(self::tableName(), $data);
                $this->id = $wpdb->insert_id;

            } else {
                $data['updated_at'] = time();
                $wpdb->update(self::tableName(), $data, ['id' => $this->id]);
            }
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
        return true;
    }

    public static function delete(int $id)
    {
        try {
            global $wpdb;
            $wpdb->delete(self::tableName(), ['id' => $id]);
        } catch (\Throwable $e) {
            w39sq_echo_thr($e);
        }
    }

    public function copy(): self
    {
        $_clone = clone $this;
        $_clone->id = null;
        $_clone->name .= '_copy';
        $_clone->save();
        return $_clone;
    }

    public function generateCode(): string
    {
        $temp = W39SQTemplates::get($this->template);
        //Обрабатываем стили
        $style = $temp->getStyle();
        foreach ($this->variables as $variable) {
            $style = str_replace('[' . $variable->variable . ']', $variable->value, $style);
        }

        $style_add = !$this->question->withInfo ? '.w39sq-block_info {display:none !important;}' : '';//Опросник без ИНФО блока
        $style = '<style>' . $style . $this->user_style . $style_add . '</style>';

        //** Обрабатываем HTML-код **/
        $code = $temp->code;

        $code = str_replace('{id}', (string)$this->Id(), $code);
        $code = str_replace('{title}', $this->title, $code);
        $code = str_replace('{description}', $this->description, $code);

        //Создаем пагинацию по кол-ву вопросов + 1 для контактных данных
        $p_i = str_repeat($temp->pagination_item, $this->question->count() + 1);
        $code = str_replace('{pagination-item}', $p_i, $code);

        $code = str_replace('{count-question}', (string)($this->question->count() + 1), $code);
        $code = str_replace('{with-cost}',($this->question->withCost ? '1' : '0') , $code);

        ///* Блок-слайдер вопросов *///
        $code = str_replace('{slider-item}', $this->question->generateCode($temp->slider_item), $code);

        $code = str_replace('{caption-cost}', __('Расчет стоимости', 'w39sq'), $code);
        $code = str_replace('{currency-cost}', $this->question->currency, $code);
        $code = str_replace('{locale-cost}', get_locale(), $code);

        return $style . $code;
    }

    public function generateResponse()//: string
    {
        if (!$this->response->generate($this->title))
            throw new \DomainException(__('Что-то пошло не так ...', 'w39sq'));
    }

    /** PRIVATE **/
    private function _load(int $id)
    {
        global $wpdb;
        $row = $wpdb->get_row('SELECT * FROM ' . self::tableName() . ' WHERE id=' . $id, ARRAY_A);
        if ($row == null) throw new \DomainException(sprintf(__('Опросник под №=%d не найден в базе.', 'w39sq'), $id));

        $this->id = (int)$row['id'];
        $this->name = $row['name'];
        $this->title = $row['title'] ?? '';
        $this->email = $row['email'] ?? '';
        $this->email_from = $row['email_from'] ?? '';
        $this->email_subject = $row['email_subject'] ?? '';
        $this->template = (int)$row['template'];
        $this->style = $row['style'] ?? '';
        $this->user_style = $row['user_style'] ?? '';
        $this->code = $row['code'] ?? '';
        $this->other = $row['other'] ?? '';
        $this->created_at = (int)$row['created_at'];
        $this->updated_at = (int)$row['updated_at'];
        $this->description = $row['description'] ?? '';

        $mapper = new JsonMapper();
        $this->question = $mapper->map(json_decode($row['question'] ?? '{}'), new W39SQQuestions());
        $this->response = $mapper->map(json_decode($row['response'] ?? '{}'), new W39SQResponse());

        foreach (json_decode($row['variables'], true) as $item) {
            $this->variables[] = new W39SQVariable(
                $item['variable'],
                $item['type'],
                $item['value'],
                $item['caption']
            );
        }
    }

    /** STATIC **/
    public static function tableName()
    {
        global $wpdb;
        return $wpdb->prefix . 'web39sq_main_table';
    }

    public static function findAll($arg = '')
    {
        global $wpdb;
        if (!empty($arg)) $arg = ' ' . $arg;

        $sql = 'SELECT id, name, title, created_at, updated_at FROM ' . self::tableName() . $arg;
        return $wpdb->get_results($sql, ARRAY_A);
    }

    public function validate()
    {
        if (empty($this->name)) return false;
        //TODO Сделать проверку

        return true;
    }

    public function getHTML()
    {
        return $this->code;
    }

    public function reloadVariables(): bool
    {
        $this->variables = [];
        $temp = W39SQTemplates::get($this->template);

        foreach ($temp->variables as $variable) {//Грузим переменные c default-значениями
            $this->variables[] = $variable;
        }
        return true;
    }

    /** @return W39SQVariable[] */
    public function getTemplateVariables(): array
    {
        $variables = [];
        $temp = W39SQTemplates::get($this->template);
        foreach ($temp->variables as $variable) {//Грузим переменные c default-значениями
            $variables[] = $variable;
        }
        return $variables;
    }

    /** @return W39SQVariable[] */
    public function getVariables(): array
    {
        return $this->variables;
    }
    public function getVariable(W39SQVariable $item): string
    {
        foreach ($this->variables as $variable) {
            if ($variable->isFor($item->variable)) {
                return $variable->value;
            }
        }
        return $item->value;
    }

    public function getTemplate(): int
    {
        return $this->template;
    }

    public function getStyle()
    {
        return $this->style;
    }
}
