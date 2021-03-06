<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Obtiene una lista de productos disponibles
     * en la base de datos.
     * 
     * @param Request request
     * @return Response
     */
    public function index(Request $request)
    {
        $offset = intval($request->query('offset', 0));
        if ($offset < 0) {
            return response()->json(['error' => 'offset value should be greater than zero'], 400);
        }

        $limit = intval($request->query('limit', 15));
        if ($limit < 0) {
            return response()->json(['error' => 'limit value should be greater than zero'], 400);
        }

        $products = Product::take($limit)->skip($offset)->get();
        return response()->json($products);
    }

    /**
     * Persiste un nuevo producto en la base de datos
     * 
     * @param Request request
     * @return Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|gte:0',
            'image' => 'required|image',
        ]);

        $product_image = $validated['image'];
        $image_name = time() . '.' . File::extension($product_image->getClientOriginalName());
        $request->file('image')->storeAs('images', $image_name);

        $product = new Product();
        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];
        $product->image = $image_name;
        $product->save();

        return response()->json($product, 201);
    }

    /**
     * Edita los campos de un producto en la base datos.
     * 
     * @param Request request
     * @param int id ID del producto a editar
     * @return Response
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|gte:0',
            'image' => 'sometimes|required|image',
        ]);

        $product = Product::find($id);
        if (is_null($product)) {
            return response()->json(['error' => "product with id $id wasn't found"], 400);
        }

        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->price = $validated['price'];

        if (array_key_exists('image', $validated)) {
            $product_image = $validated['image'];
            $image_name = time() . '.' . File::extension($product_image->getClientOriginalName());
            $request->file('image')->storeAs('images', $image_name);
            $product->image = $image_name;
        }

        $product->save();

        return response()->json($product);
    }

    /**
     * Elimina un producto de la base de datos a través
     * de su ID.
     * 
     * @param int id ID del producto a eliminar
     * @return Response
     */
    public function destroy(int $id)
    {
        $product = Product::find($id);
        if (is_null($product)) {
            return response()->json(['error' => "product with id $id wasn't found"]);
        }

        $product->delete();
        return response()->json($product);
    }

    /**
     * Busca un producto en la base de datos a través de su nombre.
     * 
     * @param Request request
     * @return Response arreglo JSON con productos que coincidieron con la búsqueda
     */
    public function search(Request $request)
    {
        $product_name = $request->query('name');

        $products = Product::where('name', 'like', '%' . $product_name . '%')->get();
        return response()->json($products);
    }

    /**
     * Retorna la cantidad de productos en la base de datos
     * @return Response objeto JSON con objeto "count" el cual
     *                  contiene un número entero
     */
    public function count()
    {
        $count = Product::count();
        return response()->json(['count' => $count]);
    }
}
