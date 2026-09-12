<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SampleShippedMail;
use App\Models\SampleRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * サンプル申し込みの受付・発送管理。
 * 「CSV出力」と「発送済み」は別操作にしている（出力しただけで発送済みにしないため）。
 */
class SampleRequestController extends Controller
{
    /** ネコポスの送り状種類コード（B2クラウド） */
    const INVOICE_TYPE_NEKOPOSU = 'A';

    /** ご依頼主（送り主）情報 */
    const SENDER_NAME = '大陸通商株式会社（うましっぽ）';
    const SENDER_POSTAL = '375-0005';
    const SENDER_ADDRESS = '群馬県藤岡市中187-1';
    const SENDER_PHONE = '0274-20-1234';

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', SampleRequest::STATUS_REQUESTED);

        $query = SampleRequest::with('affiliate')->orderByDesc('id');
        if ($status !== 'all' && array_key_exists($status, SampleRequest::$statusLabels)) {
            $query->where('status', $status);
        }

        return view('admin.sample-requests.index', [
            'requests' => $query->paginate(50)->withQueryString(),
            'status' => $status,
            'statusLabels' => SampleRequest::$statusLabels,
            'counts' => SampleRequest::selectRaw('status, COUNT(*) AS c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    /**
     * ヤマトB2クラウド取込用のCSVを出力する（未発送分をまとめて）。
     *
     * B2側の取込みパターン「ネクストエンジン-通販用」の紐付けに合わせた列順で出力する。
     * （パートさんが通常注文と同じ操作で取り込めるよう、既存パターンをそのまま使う）
     * 対応: 1=お客様管理番号 2=送り状種類 5=出荷予定日 9=お届け先電話 11=郵便番号
     *       12=住所 13=建物名 16=お届け先名 18=敬称 20/22/23/25=ご依頼主
     * 品名1・ご請求先顧客コード・分類コード・運賃管理番号はB2側の固定値が入るため出力しない。
     */
    public function downloadCsv(Request $request)
    {
        $targets = SampleRequest::with('affiliate')
            ->whereIn('status', [SampleRequest::STATUS_REQUESTED, SampleRequest::STATUS_CSV_EXPORTED])
            ->orderBy('id')
            ->get();

        if ($targets->isEmpty()) {
            return back()->with('warning', '出力対象の申し込みがありません。');
        }

        $setting = Setting::current();
        $shipDate = now()->format('Y/m/d');

        // 1行目の項目名（B2は2行目から読むため、人が見て分かるようにするためのもの）
        $header = [
            'お客様管理番号', '送り状種類', 'クール区分', '（未使用）', '出荷予定日',
            'お届け予定日', '配達時間帯区分', 'お届け先コード', 'お届け先電話番号', 'お届け先電話番号枝番',
            'お届け先郵便番号', 'お届け先住所', 'お届け先建物名', 'お届け先会社・部門1', 'お届け先会社・部門2',
            'お届け先名', 'お届け先名略称カナ', '敬称', 'ご依頼主コード', 'ご依頼主電話番号',
            'ご依頼主電話番号枝番', 'ご依頼主郵便番号', 'ご依頼主住所', 'ご依頼主建物名', 'ご依頼主名',
            'ご依頼主名略称カナ', '品名コード1', '品名1', '品名コード2', '品名2',
            '荷扱い1', '荷扱い2', '記事', 'コレクト代金引換額', 'コレクト内消費税額等',
            '営業所止置き', '営業所コード', '発行枚数', '個数口枠の印字', '（未使用）',
        ];

        $rows = [$header];
        foreach ($targets as $r) {
            $row = array_fill(0, count($header), '');
            $row[0]  = $r->managementCode();               // C01 お客様管理番号
            $row[1]  = self::INVOICE_TYPE_NEKOPOSU;        // C02 送り状種類（A=ネコポス）
            $row[4]  = $shipDate;                          // C05 出荷予定日
            $row[8]  = $r->phone;                          // C09 お届け先電話番号
            $row[10] = $r->postal_code;                    // C11 お届け先郵便番号
            $row[11] = $r->fullAddress();                  // C12 お届け先住所
            $row[12] = (string) $r->address2;              // C13 お届け先建物名
            $row[15] = $r->recipient_name;                 // C16 お届け先名
            // C18 敬称は空欄（B2側の既定に任せる）
            $row[19] = self::SENDER_PHONE;                 // C20 ご依頼主電話番号
            $row[21] = self::SENDER_POSTAL;                // C22 ご依頼主郵便番号
            $row[22] = self::SENDER_ADDRESS;               // C23 ご依頼主住所
            $row[24] = self::SENDER_NAME;                  // C25 ご依頼主名
            // C28 品名1はB2側の固定値が優先されるが、将来紐付ける場合に備えて入れておく
            $row[27] = $setting->sample_invoice_item_name;
            // クール区分・お届け予定日・配達時間帯はネコポスでは指定できないため空欄
            $rows[] = $row;
        }

        // B2の出力形式に合わせ、全項目をダブルクォートで囲む
        $lines = [];
        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(
                fn ($v) => '"'.str_replace('"', '""', (string) $v).'"',
                $row
            ));
        }
        $csv = "\xEF\xBB\xBF".implode("\r\n", $lines)."\r\n";

        // 出力済みとして記録（発送済みにはしない）
        SampleRequest::whereIn('id', $targets->pluck('id'))
            ->where('status', SampleRequest::STATUS_REQUESTED)
            ->update([
                'status' => SampleRequest::STATUS_CSV_EXPORTED,
                'csv_downloaded_at' => now(),
            ]);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sample_nekoposu_'.now()->format('Ymd_His').'.csv"',
        ]);
    }

    /**
     * B2クラウドの「発行済データ」を取り込み、伝票番号を一括登録する。
     * ヘッダー行なし・Shift_JIS・1列目=お客様管理番号・4列目=送り状番号。
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ], [], ['file' => 'ファイル']);

        $sendMail = $request->boolean('send_mail');
        $adminLabel = $this->adminLabel($request);

        $content = file_get_contents($request->file('file')->getRealPath());
        if ($content === false) {
            return back()->with('error', 'ファイルを読み込めませんでした。');
        }

        // B2の発行済データはShift_JIS。先に全体を変換してから解析する（マルチバイトの誤認を防ぐ）
        $content = mb_convert_encoding($content, 'UTF-8', 'SJIS-win');

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $registered = 0;
        $alreadyDone = 0;
        $notFound = [];

        while (($row = fgetcsv($handle)) !== false) {
            $code = trim((string) ($row[0] ?? ''));
            $id = SampleRequest::idFromManagementCode($code);
            if ($id === null) {
                continue; // サンプル以外の行（通常注文）は無視
            }

            $tracking = preg_replace('/[^0-9]/', '', (string) ($row[3] ?? ''));
            if (strlen((string) $tracking) !== 12) {
                $notFound[] = $code.'（伝票番号が不正）';
                continue;
            }

            $sampleRequest = SampleRequest::with('affiliate')->find($id);
            if (!$sampleRequest) {
                $notFound[] = $code.'（該当する申し込みなし）';
                continue;
            }
            if ($sampleRequest->status === SampleRequest::STATUS_SHIPPED) {
                $alreadyDone++; // 同じファイルを再度取り込んでも二重登録しない
                continue;
            }

            $this->markShipped($sampleRequest, $tracking, $adminLabel, $sendMail);
            $registered++;
        }
        fclose($handle);

        $message = "{$registered}件の伝票番号を登録しました。";
        if ($alreadyDone > 0) {
            $message .= "（登録済みのため{$alreadyDone}件はスキップ）";
        }
        if ($notFound !== []) {
            $message .= ' 取り込めなかった行: '.implode(' / ', array_slice($notFound, 0, 10));
        }

        return back()->with($registered > 0 ? 'success' : 'warning', $message);
    }

    /**
     * 伝票番号を個別に登録する（取り込みが使えないときの手入力用）。
     */
    public function storeTracking(Request $request, SampleRequest $sampleRequest): RedirectResponse
    {
        $data = $request->validate([
            'tracking_number' => ['required', 'string', 'max:32'],
        ], [], ['tracking_number' => '伝票番号']);

        $tracking = preg_replace('/[^0-9]/', '', $data['tracking_number']);
        if (strlen((string) $tracking) !== 12) {
            return back()->with('error', '伝票番号は数字12桁で入力してください。');
        }

        $this->markShipped(
            $sampleRequest->load('affiliate'),
            $tracking,
            $this->adminLabel($request),
            $request->boolean('send_mail', true)
        );

        return back()->with('success', '伝票番号を登録し、発送済みにしました。');
    }

    /**
     * 伝票番号なしで発送済みにする（手書き送り状などの例外対応）。
     */
    public function ship(Request $request, SampleRequest $sampleRequest): RedirectResponse
    {
        $this->markShipped(
            $sampleRequest->load('affiliate'),
            null,
            $this->adminLabel($request),
            $request->boolean('send_mail')
        );

        return back()->with('success', '発送済みにしました。');
    }

    public function cancel(Request $request, SampleRequest $sampleRequest): RedirectResponse
    {
        if ($sampleRequest->status === SampleRequest::STATUS_SHIPPED) {
            return back()->with('error', '発送済みの申し込みは取り消せません。');
        }

        $sampleRequest->update(['status' => SampleRequest::STATUS_CANCELLED]);

        return back()->with('success', '申し込みを取り消しました。');
    }

    /**
     * 発送済みにする共通処理。
     * 既存のアンバサダー側「サンプル送付済み」表示とも連動させる。
     */
    private function markShipped(SampleRequest $sampleRequest, ?string $tracking, string $adminLabel, bool $sendMail): void
    {
        $sampleRequest->status = SampleRequest::STATUS_SHIPPED;
        $sampleRequest->shipped_at = now();
        $sampleRequest->shipped_by = $adminLabel;
        if ($tracking) {
            $sampleRequest->tracking_number = $tracking;
        }
        $sampleRequest->save();

        // 既存機能（アンバサダー一覧のサンプル送付済み表示）と整合させる
        $affiliate = $sampleRequest->affiliate;
        if ($affiliate && !$affiliate->sample_sent_at) {
            $affiliate->update([
                'sample_sent_at' => now(),
                'sample_sent_by' => $adminLabel,
            ]);
        }

        if (!$sendMail || $sampleRequest->shipped_mail_sent_at || !$affiliate?->email) {
            return;
        }

        // メール送信の失敗で発送記録を巻き戻さない
        try {
            Mail::to($affiliate->email)->send(new SampleShippedMail($sampleRequest));
            $sampleRequest->update(['shipped_mail_sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('サンプル発送メールの送信に失敗しました: '.$e->getMessage());
        }
    }

    private function adminLabel(Request $request): string
    {
        $name = $request->session()->get('admin_name');
        $email = $request->session()->get('admin_email');

        return $name ?: ($email ?: '不明');
    }
}
