<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Http\Resources\V2\FlashDealCollection;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Color;
use Illuminate\Http\Request;
use App\Utility\CategoryUtility;
use App\Utility\SearchUtility;

class ProductController extends Controller
{
    public function index()
    {
        return new ProductMiniCollection(Product::where('published', 1)->latest()->paginate(12));
    }

    public function show($id)
    {
        try {
            $product = Product::where('published', 1)->findOrFail($id);
            return new ProductDetailCollection(collect([$product]));
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'success' => false,
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function admin()
    {
        return new ProductCollection(Product::where('added_by', 'admin')->where('published', 1)->latest()->paginate(12));
    }

    public function seller($id, Request $request)
    {
        $name = $request->name;
        $cacheKey = "app.products_seller_$id";
        if ($name) {
            $cacheKey .= '_' . md5($name);
        }

        $shop = Shop::findOrFail($id);
        $products = Product::where('added_by', 'seller')->where('user_id', $shop->user_id)->where('published', 1);
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        return new ProductMiniCollection($products->latest()->paginate(12));
    }

    public function category($id, Request $request)
    {
        // Get page and per_page from request, with defaults
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);

        // Ensure perPage is within reasonable limits
        $perPage = min(max($perPage, 12), 100);

        $name = $request->name;
        $cacheKey = "app.products_category_$id";
        if ($name) {
            $cacheKey .= '_' . md5($name);
        }

        // Add pagination params to cache key
        $cacheKey .= "_page_{$page}_per_{$perPage}";

        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;

        $products = Product::whereIn('category_id', $category_ids)->where('published', 1);

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        return new ProductMiniCollection(filter_products($products)->latest()->paginate($perPage, ['*'], 'page', $page));
    }


    public function brand($id, Request $request)
    {
        // Get page and per_page from request, with defaults
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);

        // Ensure perPage is within reasonable limits
        $perPage = min(max($perPage, 12), 100);

        $name = $request->name;
        $cacheKey = "app.products_brand_$id";
        if ($name) {
            $cacheKey .= '_' . md5($name);
        }

        // Add pagination params to cache key
        $cacheKey .= "_page_{$page}_per_{$perPage}";

        $products = Product::where('brand_id', $id)->where('published', 1);
        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }

        return new ProductMiniCollection(filter_products($products)->latest()->paginate($perPage, ['*'], 'page', $page));
    }

    public function todaysDeal()
    {
        $products = Product::where('todays_deal', 1)->where('published', 1);
        return new ProductMiniCollection(filter_products($products)->limit(20)->latest()->get());
    }

    public function flashDeal()
    {
        $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
        return new FlashDealCollection($flash_deals);
    }

    public function featured()
    {
        $products = Product::where('featured', 1)->where('published', 1);
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(12));
    }

    public function bestSeller()
    {
        $products = Product::where('published', 1)->orderBy('num_of_sale', 'desc');
        return new ProductMiniCollection(filter_products($products)->limit(20)->get());
    }

    public function homeProducts()
    {
        $products = Product::where('todays_deal', 1)->where('published', 1);
            $new_arrivals_products = Product::where('published', 1)->latest();
            $new_arrivals = new ProductMiniCollection(filter_products($new_arrivals_products)->limit(12)->get());

            $flash_dealss = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
            $flash_deals = new FlashDealCollection($flash_dealss);

            $fproducts = Product::where('featured', 1)->where('published', 1);
            $featured = new ProductMiniCollection(filter_products($fproducts)->latest()->paginate(12));

            // today's deal: latest 4 products with todays_deal flag (not num_of_sale sorting)
            $todays_deal_products = Product::where('todays_deal', 1)->where('published', 1);
            $todays_deal = new ProductMiniCollection(filter_products($todays_deal_products)->latest()->limit(12)->get());

            // best_selling: latest 4 products with best_selling flag (not num_of_sale sorting)
            $best_selling_products = Product::where('best_selling', 1)->where('published', 1);
            $best_selling = new ProductMiniCollection(filter_products($best_selling_products)->latest()->limit(12)->get());

             return [
                'new_arrivals' => $new_arrivals,
                'flash_deal' => $flash_deals,
                'featured' => $featured,
                'best_selling' => $best_selling,
                'todays_deal' => $todays_deal,
            ];
    }

    public function related($id)
    {
        $product = Product::where('published', 1)->find($id);
        $products = Product::where('category_id', $product->category_id)->where('id', '!=', $id)->where('published', 1);
        return new ProductMiniCollection(filter_products($products)->limit(12)->get());
    }

    public function topFromSeller($id)
    {
        $product = Product::where('published', 1)->find($id);
        $products = Product::where('user_id', $product->user_id)->where('published', 1)->orderBy('num_of_sale', 'desc');

        return new ProductMiniCollection(filter_products($products)->limit(12)->get());
    }


    public function search(Request $request)
    {
        // Get page and per_page from request, with defaults
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 12);

        // Ensure perPage is within reasonable limits
        $perPage = min(max($perPage, 12), 100);

        $name = $request->query_key;

        // Create cache key based on parameters
        $cacheKey = 'app.products_search';
        if ($name) $cacheKey .= '_name_' . md5($name);

        // Add pagination params to cache key
        $cacheKey .= "_page_{$page}_per_{$perPage}";

        $products = Product::query();

        $products->where('published', 1);

        // If name is provided, search for exact match only
        if ($name != null && $name != "") {
            $products->where('name', 'like', '%' . $name . '%'); // Partial match
            SearchUtility::store($name);
        }

        // Order by latest
        $products->orderBy('created_at', 'desc');

        // Get the paginated results
        $paginatedProducts = $products->paginate($perPage, ['*'], 'page', $page);

        // Only show "No product found" message when a search term was provided but no results were found
        if ($paginatedProducts->isEmpty() && !empty($name)) {
            // Return empty collection with a custom message
            return response()->json([
                'data' => [],
                'message' => 'No product found',
                'success' => true,
                'status' => 200
            ]);
        }

        return new ProductMiniCollection($paginatedProducts);
    }

    public function variantPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $str = '';
        $tax = 0;

        if ($request->has('color') && $request->color != "") {
            $str = Color::where('code', '#' . $request->color)->first()->name;
        }

        $var_str = str_replace(',', '-', $request->variants);
        $var_str = str_replace(' ', '', $var_str);

        if ($var_str != "") {
            $temp_str = $str == "" ? $var_str : '-' . $var_str;
            $str .= $temp_str;
        }


        $product_stock = $product->stocks->where('variant', $str)->first();
        $price = $product_stock->price;
        $stockQuantity = $product_stock->qty;


        //discount calculation
        $discount_applicable = false;

        if ($product->discount_start_date == null) {
            $discount_applicable = true;
        } elseif (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
            strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }

        if ($product->tax_type == 'percent') {
            $price += ($price * $product->tax) / 100;
        } elseif ($product->tax_type == 'amount') {
            $price += $product->tax;
        }



        return response()->json([
            'product_id' => $product->id,
            'variant' => $str,
            'price' => (double)convert_price($price),
            'price_string' => format_price(convert_price($price)),
            'stock' => intval($stockQuantity),
            'image' => $product_stock->image == null ? "" : api_asset($product_stock->image)
        ]);
    }

    public function home(Request $request)
    {
        // Get page and per_page from request, with defaults
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        // Ensure perPage is within reasonable limits
        $perPage = min(max($perPage, 12), 100);

        // Apply sorting based on the request parameter
        $sort = $request->get('sort', 'newest');

        $products = Product::query();
        $products->where('published', 1);

        switch ($sort) {
            case 'oldest':
                $products->oldest();
                break;
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;
            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;
            case 'newest':
            default:
                $products->latest();
                break;
        }

        return new ProductCollection($products->paginate($perPage, ['*'], 'page', $page));
    }
}
