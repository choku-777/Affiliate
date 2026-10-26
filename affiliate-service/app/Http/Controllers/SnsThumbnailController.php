<?php

namespace App\Http\Controllers;

use App\Models\SnsPost;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 公式サイトに表示するサムネイルの配信（公開）。
 * 公式サイトに表示される状態の投稿だけを返す。掲載停止・却下になったものは見えなくなる。
 * 停止がすぐ効くよう、ブラウザには毎回確認させる（変わっていなければ 304 で済む）。
 */
class SnsThumbnailController extends Controller
{
    public function show(SnsPost $snsPost): BinaryFileResponse
    {
        abort_unless($snsPost->isListable(), 404);

        return response()->file(storage_path('app/'.$snsPost->thumbnail_path), [
            'Content-Type' => 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, no-cache',
        ]);
    }
}
