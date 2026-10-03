<?php

namespace App\Http\Controllers;

use Auth;
use Hash;
use Log;
use Schema;
use Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    /**
     * Tables that hold demo/transactional shop activity, plus the catalog
     * (products/categories) and their child/pivot tables so nothing is left
     * pointing at a deleted product_id or category_id. Anything that would
     * break the site if emptied (users, business_settings, payment gateway
     * config, brands, attributes/colors/taxes, roles, wallets, commission
     * and withdrawal ledgers) is deliberately left out.
     */
    const CLEANUP_TABLES = [
        'order_details',
        'combined_orders',
        'orders',
        'payments',
        'transactions',
        'carts',
        'wishlists',
        'reviews',
        'landing_page_reviews',
        'messages',
        'conversations',
        'notifications',
        'firebase_notifications',
        'ticket_replies',
        'tickets',
        'coupon_usages',
        'guest_otp_codes',
        'searches',
        // Catalog data + its child/pivot tables
        'flash_deal_products',
        'landing_page_product',
        'product_stocks',
        'product_taxes',
        'product_translations',
        'products',
        'home_categories',
        'attribute_category',
        'category_translations',
        'categories',
    ];

    const CONFIRMATION_PHRASE = 'RESET DEMO DATA';

    public function databaseCleanupIndex(Request $request)
    {
        return view('backend.system.database_cleanup', [
            'tables' => self::CLEANUP_TABLES,
            'phrase' => self::CONFIRMATION_PHRASE,
            'enabled' => config('app.allow_db_reset'),
        ]);
    }

    public function databaseCleanup(Request $request)
    {
        abort_unless(config('app.allow_db_reset'), 403, 'Database reset is disabled. Set ALLOW_DB_RESET=true in .env to enable it.');

        $request->validate([
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'string'],
        ]);

        if ($request->confirmation !== self::CONFIRMATION_PHRASE) {
            flash(translate('Confirmation phrase did not match. Nothing was deleted.'))->error();
            return back();
        }

        if (!Hash::check($request->password, Auth::user()->password)) {
            flash(translate('Incorrect password. Nothing was deleted.'))->error();
            return back();
        }

        $admin = Auth::user();
        $cleared = [];

        // TRUNCATE is DDL and auto-commits in MySQL, so it cannot be wrapped
        // in DB::transaction() (that raises "there is no active transaction"
        // once the implicit commit closes it underneath Laravel).
        Schema::disableForeignKeyConstraints();

        foreach (self::CLEANUP_TABLES as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $cleared[] = $table;
            }
        }

        Schema::enableForeignKeyConstraints();

        // The dashboard (and other pages) cache aggregates computed from the
        // tables we just emptied (e.g. 'cached_graph_data' is kept for 24h).
        // Without clearing it, stale numbers get shown against categories/
        // products that no longer exist, which is what produced the
        // "undefined" chart label. Clear it in the same request so the
        // admin never sees stale data from before the reset.
        Artisan::call('cache:clear');

        Log::warning('Admin database reset performed', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'tables' => $cleared,
            'ip' => $request->ip(),
            'at' => now()->toDateTimeString(),
        ]);

        flash(translate('Demo data cleared successfully. Tables affected: ') . implode(', ', $cleared))->success();
        return redirect()->route('database-cleanup.index');
    }
}
