<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Slug implements Rule
{
    protected string $message;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($this->hasUnderscores($value))
        {
            $this->message = __('validation.no_underscore');
            return false;
        }

        if ($this->startWithDash($value))
        {
            $this->message = __('validation.no_starting_dashes');
            return false;
        }

        if ($this->endWithDash($value))
        {
            $this->message = __('validation.no_ends_with_dash');
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @param mixed $value
     * @return false|int
     */
    public function hasUnderscores(mixed $value): int|false
    {
        return preg_match('/_/', $value);
    }

    /**
     * @param mixed $value
     * @return false|int
     */
    public function startWithDash(mixed $value): int|false
    {
        return preg_match('/^-/', $value);
    }

    /**
     * @param mixed $value
     * @return false|int
     */
    public function endWithDash(mixed $value): int|false
    {
        return preg_match('/-$/', $value);
    }
}
