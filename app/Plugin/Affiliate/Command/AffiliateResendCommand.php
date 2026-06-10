<?php

namespace Plugin\Affiliate\Command;

use Plugin\Affiliate\Service\PostbackClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 送信に失敗してアウトボックスに残ったイベントを再送する。
 *
 *   bin/console affiliate:resend
 *
 * 数分おきの cron 実行を推奨（送信先の一時的な障害に備える）。
 */
class AffiliateResendCommand extends Command
{
    protected static $defaultName = 'affiliate:resend';

    private $postbackClient;

    public function __construct(PostbackClient $postbackClient)
    {
        parent::__construct();
        $this->postbackClient = $postbackClient;
    }

    protected function configure()
    {
        $this->setDescription('送信失敗で残ったアフィリエイトイベントを再送します。');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->postbackClient->resendAll();

        $io->success(sprintf('再送成功: %d件 / 失敗: %d件', $result['success'], $result['failed']));

        return 0;
    }
}
