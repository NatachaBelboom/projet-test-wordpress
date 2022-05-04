<?php

class RequiredValidator extends BaseValidator
{
    protected function handle($value) : ?string
    {
        if(is_null($value) || $value === '' || (is_array($value) && empty($value)))
        {
            return __('Ce champ ne peut pas etre vide', 'dw');
        }

        return null;
    }
}