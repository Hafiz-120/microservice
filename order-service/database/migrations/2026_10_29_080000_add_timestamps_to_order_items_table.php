<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [];

        if (! Schema::hasColumn('order_items', 'created_at')) {
            $columns[] = 'created_at';
        }

        if (! Schema::hasColumn('order_items', 'updated_at')) {
            $columns[] = 'updated_at';
        }

        if ($columns === []) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) use ($columns): void {
            foreach ($columns as $column) {
                $table->timestamp($column)->nullable();
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['created_at', 'updated_at'],
            fn (string $column): bool => Schema::hasColumn('order_items', $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('order_items', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
