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
        if ($request->has('q')) {
            if (!empty($q = $request->get('q'))) {
                $result = $this->index->search($q);
                $result = [
                    'count' => $result->getCount(),
                    'documents' => $result->getDocuments(),
                ];
                return response()->json($result, 200);
            }
        }

        $products = Product::all();

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
        return response()->json([], 201);
    }

    public function update(Request $request, $id)
    {
        return response()->json([], 200);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json([], 200);
    }

    public function suggestion(Request $request)
    {
        if ($request->has('q')) {
            if (!empty($q = $request->get('q'))) {

                $suggestionIndex = new Suggestion($this->adapter, Product::INDEX);

                $result = [
                    'suggestions' => $suggestionIndex->get($q),
                ];

                return response()->json($result, 200);

            }
        }

        return response()->json(['suggestions' => []], 200);
    }
}
