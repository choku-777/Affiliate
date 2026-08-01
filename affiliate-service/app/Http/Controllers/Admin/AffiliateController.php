<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AffiliateApproved;
use App\Models\Affiliate;
use App\Models\AffiliateNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function index(Request $request): View
    {
        $query = Affiliate::query()->orderByDesc('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($keyword = $request->query('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        if ($sample = $request->query('sample')) {
            if ($sample === 'sent') {
                $query->whereNotNull('sample_sent_at');
            } elseif ($sample === 'unsent') {
                $query->whereNull('sample_sent_at');
            }
        }

        return view('admin.affiliates.index', [
            'affiliates' => $query->paginate(20)->withQueryString(),
            'statusLabels' => Affiliate::$statusLabels,
            'filters' => $request->only('status', 'keyword', 'sample'),
        ]);
    }

    public function show(Affiliate $affiliate): View
    {
        return view('admin.affiliates.show', [
            'affiliate' => $affiliate,
            'notes' => $affiliate->notes()->get(),
        ]);
    }

    public function approve(Affiliate $affiliate)
    {
        $affiliate->update([
            'status' => Affiliate::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        try {
            Mail::to($affiliate->email)->send(new AffiliateApproved($affiliate));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('warning', '承認しましたが、通知メールの送信に失敗しました。');
        }

        return back()->with('success', '承認し、発行URLを申請者へ通知しました。');
    }

    public function reject(Affiliate $affiliate)
    {
        $affiliate->update(['status' => Affiliate::STATUS_REJECTED]);

        return back()->with('success', '却下しました。');
    }

    public function suspend(Affiliate $affiliate)
    {
        $affiliate->update(['status' => Affiliate::STATUS_SUSPENDED]);

        return back()->with('success', '停止しました。');
    }

    /**
     * サンプルを送付済みにする。
     */
    public function markSampleSent(Request $request, Affiliate $affiliate)
    {
        $affiliate->update([
            'sample_sent_at' => now(),
            'sample_sent_by' => $this->adminLabel($request),
        ]);

        return back()->with('success', 'サンプルを送付済みにしました。');
    }

    /**
     * サンプル送付の記録を取り消す。
     */
    public function unmarkSampleSent(Affiliate $affiliate)
    {
        $affiliate->update([
            'sample_sent_at' => null,
            'sample_sent_by' => null,
        ]);

        return back()->with('success', 'サンプル送付の記録を取り消しました。');
    }

    /**
     * メモを追加する。
     */
    public function storeNote(Request $request, Affiliate $affiliate)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ], [
            'body.required' => 'メモを入力してください。',
            'body.max' => 'メモは2000文字以内で入力してください。',
        ], ['body' => 'メモ']);

        $affiliate->notes()->create([
            'body' => $validated['body'],
            'created_by' => $request->session()->get('admin_email'),
            'created_by_name' => $request->session()->get('admin_name'),
        ]);

        return back()->with('success', 'メモを追加しました。');
    }

    /**
     * メモを削除する。
     */
    public function destroyNote(Affiliate $affiliate, AffiliateNote $note)
    {
        // 他のアンバサダーのメモを消せないようにする
        if ($note->affiliate_id !== $affiliate->id) {
            abort(404);
        }

        $note->delete();

        return back()->with('success', 'メモを削除しました。');
    }

    /**
     * 操作した管理者の表示名（「名前（メール）」形式）。
     */
    private function adminLabel(Request $request): string
    {
        $name = $request->session()->get('admin_name');
        $email = $request->session()->get('admin_email');

        if ($name && $email) {
            return "{$name}（{$email}）";
        }

        return (string) ($name ?: $email);
    }
}
