<?php

/**
 * Merge data with ID.
 *
 * @param $data
 * @param $id
 * @return array
 */
function compareId($data, $id)
{
    unset($data['id']);

    return array_merge($data, ['id' => $id]);
}

/**
 * Create the data array of a request.
 *
 * @param $token
 * @param $method
 * @param $data
 * @return array
 */
function prepare($token, $method, $data = [])
{
    return array_merge([
        'token'   => $token,
        '_method' => $method,
    ], $data);
}

/**
 * Make a Model using ModelFactory.
 *
 * @param $class
 * @param array $attributes
 * @return mixed
 */
function make($class, $attributes = [])
{
    return factory($class)->make($attributes);
}

/**
 * Create a Model using ModelFactory.
 *
 * @param $class
 * @param array $attributes
 * @return mixed
 */
function create($class, $attributes = [])
{
    return factory($class)->create($attributes);
}

/**
 * Mask Number into String
 *
 * @param $value
 * @param $mask
 * @return string
 */
function mask($value, $mask)
{
    $maskared = '';
    $k = 0;
    for ($i = 0; $i <= strlen($mask) - 1; $i ++) {
        if ($mask[$i] == '#') {
            if (isset($value[$k]))
                $maskared .= $value[$k ++];
        } else {
            if (isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }

    return $maskared;
}