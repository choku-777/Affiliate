<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SnsPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 公式サイト（EC-CUBE）向け：掲載してよいアンバサダー投稿の一覧（X-Api-Key 認証）。
 *
 * 返すのは表示に必要な項目だけ。アンバサダーの氏名・連絡先・住所などの個人情報は含めない。
 * サムネイルは相対パスで返す（EC-CUBE 側が AFFILIATE_API_URL を前に付けて使う）。
 */
class SnsPostListController extends Controller
{
    private const LIMIT = 50;

    public function index(Request $request): JsonResponse
    {
        $site = Site::byCode((string) $request->query('site', ''));
        if (!$site) {
            return response()->json(['message' => 'Unknown site'], 404);
        }

        // 画像ファイルが欠けた投稿を除きながら、表示順に50件集まった時点で打ち切る
        $posts = SnsPost::listedOn($site)->with(['affiliate', 'site'])->lazy(100)
            ->filter(fn (SnsPost $p) => $p->hasThumbnail())
            ->take(self::LIMIT)
            ->collect()
            ->values()
            ->map(fn (SnsPost $p) => [
                'id' => $p->id,
                'platform' => $p->platform,
                'platform_label' => $p->platformLabel(),
                'url' => $p->normalized_url,
                'post_id' => $p->post_id,
                'product_id' => $p->product_id,
                'product_name' => $p->product_name,
                'dog' => [
                    'breed' => $p->dog_breed,
                    'age' => $p->dog_age,
                    'size' => $p->dog_size,
                    'size_label' => $p->dogSizeLabel(),
                    'stage' => $p->dog_stage,
                    'stage_label' => $p->dogStageLabel(),
                ],
                'thumbnail_path' => route('sns-thumbs.show', $p, false).'?v='.$p->thumbnail_fetched_at?->timestamp,
                'approved_at' => $p->approved_at?->toIso8601String(),
            ]);

        return response()->json([
            'site' => $site->code,
            'generated_at' => now()->toIso8601String(),
            'posts' => $posts,
        ]);
    }
}
