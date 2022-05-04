
<?php

class EmailValidator extends BaseValidator
{
    protected function handle($value) : ?string
    {
        if(! filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            return __('Merci de fournir une adresse mail valide', 'dw');
        }

        return null;
    }
}