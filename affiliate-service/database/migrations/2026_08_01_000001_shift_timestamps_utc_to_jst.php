<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * APP_TIMEZONE=Asia/Tokyo への切り替えに伴う既存データの補正。
 *
 * これまで APP_TIMEZONE が未設定（＝Laravel既定のUTC）だったため、日時カラムには
 * UTCの壁時計時刻が保存されている。アプリを日本時間に切り替えると、保存済みの値だけが
 * 9時間ずれたまま残るため、既存行を +9時間 シフトして基準を揃える。
 *
 * 注意:
 * - .env に APP_TIMEZONE=Asia/Tokyo を設定してから実行すること。
 * - birth_date（date型・利用者の入力値）は時刻を持たないためシフトしない。
 * - migrations テーブルで管理されるため二重実行はされない。
 */
return new class extends Migration
{
    /** テーブルごとの対象カラム（timestamp型のみ） */
    private const TARGETS = [
        'affiliates' => ['created_at', 'updated_at', 'approved_at'],
        'rewards' => ['created_at', 'updated_at', 'converted_at', 'confirmed_at', 'paid_at'],
        'clicks' => ['created_at', 'updated_at', 'clicked_at'],
        'payouts' => ['created_at', 'updated_at', 'paid_at', 'csv_downloaded_at'],
        'settings' => ['created_at', 'updated_at'],
        'sites' => ['created_at', 'updated_at'],
    ];

    public function up(): void
    {
        $this->shift(9);
    }

    public function down(): void
    {
        $this->shift(-9);
    }

    private function shift(int $hours): void
    {
        foreach (self::TARGETS as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->whereNotNull($column)
                    ->update([$column => DB::raw("DATE_ADD(`{$column}`, INTERVAL {$hours} HOUR)")]);
            }
        }
    }
};
