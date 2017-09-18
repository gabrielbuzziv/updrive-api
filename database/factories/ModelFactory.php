<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/**
 * User Factory.
 */
$factory->define('App\User', function (Faker\Generator $faker) {
    return [
        'name'           => $faker->name,
        'email'          => $faker->unique()->safeEmail,
        'password'       => 'secret',
        'remember_token' => str_random(10),
        'is_contact'     => false,
        'is_active'      => false,
    ];
});

/**
 * Role Factory.
 */
$factory->define('App\Role', function (Faker\Generator $faker) {
    $name = $faker->name;

    return [
        'name'         => str_slug($name),
        'display_name' => $name,
    ];
});

/**
 * Permission Factory.
 */
$factory->define('App\Permission', function (Faker\Generator $faker) {
    $name = $faker->name;

    return [
        'name'         => str_slug($name),
        'display_name' => $name,
        'description'  => $faker->text(200),
    ];
});

/**
 * Company Factory.
 */
$factory->define('App\Company', function (Faker\Generator $faker) {
    $taxvat = (string) mt_rand(00000000000001, 99999999999999);
    $docnumber = (string) mt_rand(000000001, 999999999);
    $docnumber_town = (string) mt_rand(000001, 999999);

    return [
        'name'            => "{$faker->company} {$faker->companySuffix}",
        'nickname'        => $faker->company,
        'taxvat'          => mask($taxvat, '##.###.###/####-##'),
        'docnumber'       => mask($docnumber, '###.###.###'),
        'docnumber_town'  => $docnumber_town,
        'email'           => $faker->email,
        'phone'           => $faker->phoneNumber,
        'customer_number' => $faker->randomNumber(3),
        'customer_branch' => $faker->randomNumber(1),
    ];
});

/**
 * CompanyAddress Factory.
 */
$factory->define('App\CompanyAddress', function (Faker\Generator $faker) {
    return [
        'postcode'   => $faker->postcode,
        'street'     => $faker->streetName,
        'number'     => $faker->buildingNumber,
        'complement' => $faker->streetSuffix,
        'district'   => $faker->citySuffix,
        'city'       => $faker->city,
        'state'      => $faker->stateAbbr,
    ];
});

/**
 * ContactAddress Factory.
 */
$factory->define('App\ContactAddress', function (Faker\Generator $faker) {
    return [
        'postcode'   => $faker->postcode,
        'street'     => $faker->streetName,
        'number'     => $faker->buildingNumber,
        'complement' => $faker->streetSuffix,
        'district'   => $faker->citySuffix,
        'city'       => $faker->city,
        'state'      => $faker->stateAbbr,
    ];
});

/**
 * Folder Factory
 */
$factory->define('App\Folder', function (Faker\Generator $faker) {
    return [
        'name'       => $faker->word,
        'company_id' => function () {
            return factory('App\Company')->create()->id;
        },
    ];
});

/**
 * Document Factory
 */
$factory->define('App\Document', function (Faker\Generator $faker) {

    return [
        'user_id'   => function () {
            return factory('App\User')->create()->id;
        },
        'folder_id' => function () {
            return factory('App\Folder')->create()->id;
        },
        'name'      => $faker->name,
        'filename'  => sprintf('%s.%s', str_random(), 'jpg'),
        'cycle'     => \Carbon\Carbon::now()->addMonth()->format('m/Y'),
        'validity'  => \Carbon\Carbon::now()->addMonth()->format('d/m/Y'),
        'note'      => $faker->text(50),
        'status'    => 1,
    ];
});