<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AffiliateApproved;
use App\Models\Affiliate;
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

        return view('admin.affiliates.index', [
            'affiliates' => $query->paginate(20)->withQueryString(),
            'statusLabels' => Affiliate::$statusLabels,
            'filters' => $request->only('status', 'keyword'),
        ]);
    }

    public function show(Affiliate $affiliate): View
    {
        return view('admin.affiliates.show', [
            'affiliate' => $affiliate,
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
}
