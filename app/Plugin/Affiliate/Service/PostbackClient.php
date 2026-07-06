<?php

namespace Plugin\Affiliate\Service;

/**
 * アフィリエイト管理サービスへ成果等を送信するクライアント。
 *
 * 信頼性のため「先にアウトボックス（ファイル）へ書き出し → 送信成功で削除」とし、
 * 送信失敗時はファイルが残るため、affiliate:resend コマンドで再送できる。
 * 独自DBテーブルを持たないので EC-CUBE 4.2/4.3 のいずれでも安全に動作する。
 */
class PostbackClient
{
    /** 送信先のパス */
    const ENDPOINT_PATH = '/api/affiliate/event';

    private $apiUrl;
    private $apiKey;
    private $outboxDir;

    public function __construct(Config $config, string $projectDir)
    {
        $this->apiUrl = $config->apiUrl();
        $this->apiKey = $config->apiKey();
        $this->outboxDir = $projectDir.'/var/affiliate_outbox';
    }

    /**
     * イベントを送信する。失敗してもアウトボックスに残るため取りこぼさない。
     *
     * @return bool 送信に成功したか
     */
    public function send(string $type, array $payload): bool
    {
        $envelope = [
            'type' => $type,
            'payload' => $payload,
            'created_at' => (new \DateTime())->format(\DateTime::ATOM),
        ];

        $file = $this->writeOutbox($envelope);

        $ok = $this->post($envelope);
        if ($ok && $file && is_file($file)) {
            @unlink($file);
        }

        return $ok;
    }

    /**
     * アウトボックスに残っているイベントを再送する。
     *
     * @return array{success:int, failed:int}
     */
    public function resendAll(): array
    {
        $success = 0;
        $failed = 0;

        foreach (glob($this->outboxDir.'/*.json') ?: [] as $file) {
            $json = @file_get_contents($file);
            $envelope = $json ? json_decode($json, true) : null;
            if (!is_array($envelope)) {
                // 壊れたファイルは退避
                @rename($file, $file.'.broken');
                $failed++;
                continue;
            }

            if ($this->post($envelope)) {
                @unlink($file);
                $success++;
            } else {
                $failed++;
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }

    /**
     * エンベロープをアウトボックスへ書き出し、ファイルパスを返す。
     */
    private function writeOutbox(array $envelope): ?string
    {
        if (!is_dir($this->outboxDir)) {
            @mkdir($this->outboxDir, 0775, true);
        }
        if (!is_dir($this->outboxDir) || !is_writable($this->outboxDir)) {
            log_error('[Affiliate] アウトボックスに書き込めません。', ['dir' => $this->outboxDir]);

            return null;
        }

        $name = sprintf('%s_%s_%s.json', $envelope['type'], date('YmdHis'), bin2hex(random_bytes(4)));
        $path = $this->outboxDir.'/'.$name;
        @file_put_contents($path, json_encode($envelope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return is_file($path) ? $path : null;
    }

    /**
     * 実際の送信（cURL）。2xx を成功とみなす。
     */
    private function post(array $envelope): bool
    {
        if (empty($this->apiUrl)) {
            log_warning('[Affiliate] AFFILIATE_API_URL が未設定のため送信をスキップしました（アウトボックスに保持）。');

            return false;
        }

        $url = $this->apiUrl.self::ENDPOINT_PATH;
        $body = json_encode($envelope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-Api-Key: '.(string) $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($status >= 200 && $status < 300) {
            return true;
        }

        log_error('[Affiliate] postback送信に失敗しました。', [
            'url' => $url,
            'status' => $status,
            'curl_error' => $error,
            'response' => is_string($response) ? mb_substr($response, 0, 500) : null,
        ]);

        return false;
    }
}
