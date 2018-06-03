<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Ehann\RedisRaw\PredisAdapter;
use Ehann\RediSearch\Index;
use Ehann\RediSearch\Fields\TextField;
use Ehann\RediSearch\Fields\NumericField;
use Ehann\RediSearch\Suggestion;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // create redis client adapter instance
        $this->adapter = (new PredisAdapter())->connect(env('REDIS_HOST'), env('REDIS_PORT'));

        // create product index
        $this->index = new Index($this->adapter);
        $this->index->addTextField('name')
            ->addTextField('slug')
            ->addNumericField('stock')
            ->addNumericField('price')
            ->addTextField('description')
            ->create();
    }

    public function index(Request $request)
    {
        if ($request->has('q') && !empty($q = $request->get('q'))) {

            // seems the sortBy is not working (in progress)
            if ($request->has('sortBy') && !empty($sortBy = $request->get('sortBy'))) {
                $this->index->sortBy($sortBy);
            }

            // perform the search
            $result = $this->index->search($q);

            // prepare the result
            $result = [
                'count' => $result->getCount(),
                'documents' => $result->getDocuments(),
            ];

            return response()->json($result, 200);
        }

        // get all products
        $products = Product::all();

        // prepare the result
        $result = [
            'count' => $products->count(),
            'documents' => $products->toArray(),
        ];

        return response()->json($result, 200);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product, 200);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $product = new Product;
        $product->name = $input['name'];
        $product->slug = str_slug($input['name']);
        $product->stock = $input['stock'];
        $product->price = $input['price'];
        $product->description = $input['description'];
        $product->save();

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $product = Product::findOrFail($id);
        $product->name = $input['name'];
        $product->slug = str_slug($input['name']);
        $product->stock = $input['stock'];
        $product->price = $input['price'];
        $product->description = $input['description'];
        $product->save();

        return response()->json($product, 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([], 200);
    }

    public function suggestion(Request $request)
    {
        if ($request->has('q') && !empty($q = $request->get('q'))) {

            // create suggestion index
            $suggestionIndex = new Suggestion($this->adapter, Product::INDEX);

            // prepare the result
            $result = [
                'suggestions' => $suggestionIndex->get($q),
            ];

            return response()->json($result, 200);
        }

        return response()->json(['suggestions' => []], 200);
    }

    public function average(Request $request, $field)
    {
        // seems the aggregation is not working ... (in progress)
        $results = $this->index->makeAggregateBuilder()
          ->groupBy('name')
          ->avg('price');

        dd($results);

        return response()->json($results, 200);
    }
}
