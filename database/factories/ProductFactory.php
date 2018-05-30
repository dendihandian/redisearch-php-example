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

use Ehann\RedisRaw\PredisAdapter;
use Ehann\RediSearch\Index;
use Ehann\RediSearch\Fields\TextField;
use Ehann\RediSearch\Fields\NumericField;
use Ehann\RediSearch\Suggestion;

$factory->define(App\Models\Product::class, function (Faker\Generator $faker) {

    // generate dummies
    $name = $faker->words($nb = 5, $asText = true);
    $slug = str_slug($name);
    $stock = rand(1,10);
    $price = rand(10,100);
    $description = $faker->paragraphs($nb = 3, $asText = true);

    // create redis adapter instance
    $redis = (new PredisAdapter())->connect(env('REDIS_HOST'), env('REDIS_PORT'));

    // create product index
    $productIndex = new Index($redis);
    $productIndex->addTextField('name')
        ->addTextField('slug')
        ->addNumericField('stock')
        ->addNumericField('price')
        ->addTextField('description')
        ->create();

    // insert to redis
    $productIndex->add([
        new TextField('name', $name),
        new TextField('slug', $slug),
        new NumericField('stock', $stock),
        new NumericField('price', $price),
        new TextField('description', $description),
    ]);

    // create suggestion index
    $productSuggestionIndex = new Suggestion($redis, \App\Models\Product::INDEX);
    $productSuggestionIndex->add($name, 1.0);

    // insert to database
    return [
        'name' => $name,
        'slug' => $slug,
        'stock' => $stock,
        'price' => $price,
        'description' => $description,
    ];
});
