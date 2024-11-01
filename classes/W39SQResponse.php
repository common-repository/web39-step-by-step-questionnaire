<?php
declare(strict_types=1);

require_once W39SQ_PLUGIN_DIR . '/classes/W39SQAdmin.php';

class W39SQResponse
{
    public string $title = '';
    public string $description = '';

    public int $type_contact = self::CONTACT_NONE;
    public string $contact = '';

    const CONTACT_NONE = 0;
    const CONTACT_PHONE = 1;
    const CONTACT_EMAIL = 2;

    public function generate(string $main_title): bool
    {
        $contact = '';
        if ($this->isPhone()) $contact = '<a href="tel:' . $this->phone($this->contact) . '"><i class="fa fa-phone" aria-hidden="true"></i> ' . $this->contact . '</a>';
        if ($this->isEmail()) $contact = '<a href="mailto:'. $this->contact . '."><i class="fa fa-envelope" aria-hidden="true"></i> ' . $this->contact . '</a>';
//ob_start()
        ?>
        <div class="w39sq-response">
            <div class="title-response"><?php echo esc_html($this->title) ?></div>
            <div class="description-response"><?php echo wp_kses($this->description, W39SQAdmin::FILTER)?></div>
            <div class="contact-response"><?php echo wp_kses($contact, array_merge(W39SQAdmin::FILTER_URL, W39SQAdmin::FILTER)) ?></div>
        </div>
        <?php
        return true;// ob_get_clean();
    }

    public function isPhone(): bool
    {
        return $this->type_contact == self::CONTACT_PHONE;
    }

    public function isEmail(): bool
    {
        return $this->type_contact == self::CONTACT_EMAIL;
    }

    public function notContact(): bool
    {
        return $this->type_contact == self::CONTACT_NONE;
    }

    private function phone(string $phone): string
    {
        return str_replace([' ', '-', '(', ',', '.'], '', $phone);
    }
}