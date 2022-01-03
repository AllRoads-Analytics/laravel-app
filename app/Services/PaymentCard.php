<?php namespace App\Services;

class PaymentCard {
    protected $array;

    public function __construct(array $array) {
        $this->array = $array;
    }

    public static function init(array $array) {
        return new self($array);
    }

    public function getBrandIcon() {
        switch ($brand = $this->array['brand']) {
            case 'visa':
                return '<i class="fab fa-cc-visa"></i>';

            case 'mastercard':
                return '<i class="fab fa-cc-mastercard"></i>';

            case 'discover':
                return '<i class="fab fa-cc-discover"></i>';

            case 'amex':
                return '<i class="fab fa-cc-amex"></i>';

            default:
                return '[' . ucfirst($brand) . ']';
        }
    }

    public function getObscuredNumber() {
        return '....' . $this->array['last4'];
    }

    public function getExpirationDate() {
        return $this->array['exp_month'] . '/' . $this->array['exp_year'];
    }
}
