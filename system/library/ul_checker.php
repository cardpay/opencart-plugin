<?php

namespace Unlimit;

class ULFormChecker
{
    public $errors = [];

    protected $rules = [
        'cardNumber' => ['/[\d]{16,19}/', 'i', ['cue205', 'cueE301']],
        'cardholderName' => ['/[\w ]{2,}/i', 's', ['cue221', 'cue316']],
        'cardExpirationMonth' => ['/[\d]{1,2}/', 'i', ['cue208', 'cue209']],
        'cardExpirationYear' => ['/[\d]{4}/', 'i', ['cue325', 'cue326']],
        'securityCode' => ['/[\d]{3}/', 'i', ['cue224', 'cueE302']],
        'docnumber' => ['/[\d]{11}/', 'i', ['cue214', 'cue324']],
        'installments' => ['/[\d]{1,2}/', 'i', ['cue220', 'cue220']],
    ];

    protected $language;

    public function __construct($language)
    {
        $this->language = $language;
    }

    public function check($data): array
    {
        $this->errors = [];

        foreach ($data as $key => $value) {
            $rule = $this->rules[$key] ?? [];
            if (empty($rule)) {
                continue;
            }

            //remove mask
            if ($rule[1] === 'i') {
                $value = preg_replace('/\D/', '', $value);
            }

            if (empty($value)) {
                $this->errors[] = $this->language->get($rule[2][0]);
            } else {
                $this->check_value($value, $rule, $key);
            }
        }

        return $this->errors;
    }

    protected function check_value($value, $rule, $key): void
    {
        switch ($key) {
            case 'cardNumber':
                $error = !$this->luhn_algorithm($value);
                break;
            case 'docnumber':
                $error = !$this->is_valid_cpf($value);
                break;
            default:
                $error = !preg_match($rule[0], $value);
        }

        if ($error) {
            $this->errors[] = $this->language->get($rule[2][1]);
        }
    }

    /**
     * @param string $cpf
     * @return bool
     */
    protected function is_valid_cpf(string $cpf): bool
    {
        return (strlen(preg_replace('/\D/', '', $cpf)) === 11);
    }

    /**
     * @param string $digit
     * @return bool
     */
    protected function luhn_algorithm(string $digit): bool
    {
        $number = strrev(preg_replace('/\D/', '', $digit));
        $sum = 0;
        for ($i = 0, $j = strlen($number); $i < $j; $i++) {
            if (($i % 2) === 0) {
                $val = $number[$i];
            } else {
                $val = $number[$i] * 2;
                if ($val > 9) {
                    $val -= 9;
                }
            }
            $sum += $val;
        }
        return (($sum % 10) === 0);
    }
}
