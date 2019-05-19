<?php


namespace FoF\BanIPs\Validators;

use Flarum\Foundation\AbstractValidator;

class BannedIPValidator extends AbstractValidator
{
    protected $rules = [
        'userId' => ['required', 'integer'],
        'address' => ['required', 'ip'],
        'reason' => ['nullable', 'string']
    ];
}