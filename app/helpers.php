<?php

use Ehann\RedisRaw\PredisAdapter;
use Ehann\RediSearch\Index;
use Ehann\RediSearch\Fields\TextField;
use Ehann\RediSearch\Fields\NumericField;
use Ehann\RediSearch\Suggestion;

if (! function_exists('generate_products')) {
    function generate_products($numberOfProducts = 1) {
        if ($numberOfProducts > 0) {
            $products = factory(\App\Models\Product::class, $numberOfProducts)->create();
            $products->each(function ($product, $key) {
                // create redis adapter instance
                $redis = (new PredisAdapter())->connect(env('REDIS_HOST'), env('REDIS_PORT'));

                // create product index
                $productIndex = new Index($redis);
                $productIndex->addNumericField('_id')
                    ->addTextField('name')
                    ->addTextField('slug')
                    ->addNumericField('stock')
                    ->addNumericField('price')
                    ->addTextField('description')
                    ->create();

                // create redis searchable product
                $productIndex->add([
                    new NumericField('_id', $product->id),
                    new TextField('name', $product->name),
                    new TextField('slug', $product->slug),
                    new NumericField('stock', $product->stock),
                    new NumericField('price', $product->price),
                    new TextField('description', $product->description),
                ]);

                // create suggestion index
                $productSuggestionIndex = new Suggestion($redis, \App\Models\Product::INDEX);

                // create redis suggestable product name
                $productSuggestionIndex->add($product->name, 1.0);
            });
        }

        return true;
    }
}
