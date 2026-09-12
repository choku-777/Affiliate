<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * マイページに掲載するお知らせ。公開中のものだけを表示順に並べる。
 */
class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 本文を表示用HTMLにする。
     * 先にエスケープしてからURLだけをリンク化するため、HTMLの混入（XSS）は起きない。
     */
    public function bodyHtml(): string
    {
        $escaped = e($this->body);
        $linked = preg_replace(
            '/(https?:\/\/[^\s<]+)/u',
            '<a href="$1" target="_blank" rel="noopener">$1</a>',
            $escaped
        );

        return nl2br($linked);
    }

    /**
     * 公開中のお知らせを表示順（同順なら新しい順）で取得する。
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }
}
