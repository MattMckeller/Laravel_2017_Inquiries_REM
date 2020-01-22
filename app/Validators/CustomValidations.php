<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 12/5/16
 * Time: 9:02 AM
 */
use Illuminate\Support\Facades\Validator;

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'phoneRegex',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/i", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'nameRegex',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[a-z ,.'-]+$/i", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'addressRegex',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[a-zA-Z0-9\s\,\.\'\-\"\_]+$/i", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'city',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[a-zA-Z0-9\s\,\.\'\-\"\_]+$/i", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'state',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[a-zA-Z0-9\s\,\.\'\-\"\_]+$/i", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'country',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[a-zA-Z0-9\s\,\.\'\-\"\_]+$/i", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'zip',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^\d{5}$|^\d{5}-\d{4}$/i", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'date',
    function ($attribute, $value, $parameters, $validator) {
        return preg_match("/(^(0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])[\/\-]\d{4}$)|(^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$)/", $value);

    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'routing_number',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^((0[0-9])|(1[0-2])|(2[1-9])|(3[0-2])|(6[1-9])|(7[0-2])|80)([0-9]{7})$/", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'account_number',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^\w{1,17}$/", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'address_text',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[a-zA-Z0-9\s\,\.\'\-\"\_]+$/i", $value);
    }
);
/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'full_text',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[A-Za-z0-9 \!\"\#$\%\&\'\(\)\*\+\,\-\.\/\\\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}\~]+/", $value);
    }
);

/** @var \Illuminate\Validation\Factory $validator */
Validator::extend(
    'simple_Text',
    function ($attribute, $value, $parameters, $validator)
    {
        return preg_match("/^[a-z ,.'-]+$/i", $value);
    }
);