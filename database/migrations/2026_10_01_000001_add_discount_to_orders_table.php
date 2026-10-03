<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountToOrdersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('orders', 'discount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('discount', 20, 2)->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('orders', 'discount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('discount');
            });
        }
    }
}
