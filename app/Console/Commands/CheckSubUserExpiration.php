<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckSubUserExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subusers:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '檢查子帳號到期日，通知即將到期帳號並停用已過期帳號';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('開始檢查子帳號到期狀態...');

        // 找出所有角色為子帳號的用戶
        $subUsers = User::where('role', 'sub_user')->get();

        $expiringUsers = [];
        $expiredCount = 0;

        foreach ($subUsers as $user) {
            // 跳過沒有設定到期日的帳號
            if (!$user->expires_at) {
                continue;
            }

            $daysUntilExpiration = Carbon::now()->diffInDays($user->expires_at, false);

            // 找出即將在一週內到期的帳號
            if ($daysUntilExpiration >= 0 && $daysUntilExpiration <= 7) {
                $expiringUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'expires_at' => $user->expires_at->format('Y-m-d'),
                    'parent_name' => $user->parentUser ? $user->parentUser->name : '無',
                    'days_left' => $daysUntilExpiration
                ];
            }

            // 找出已過期但仍然啟用的帳號
            if ($daysUntilExpiration < 0 && $user->active) {
                $user->active = false;
                $user->save();
                $expiredCount++;
            }
        }

        // 如果有即將到期的帳號，發送通知郵件
        if (count($expiringUsers) > 0) {
            $this->sendExpirationNotification($expiringUsers);
            $this->info('已發送到期通知郵件，共 ' . count($expiringUsers) . ' 個即將到期帳號');
        } else {
            $this->info('沒有即將到期的帳號');
        }

        $this->info('已自動停用 ' . $expiredCount . ' 個過期帳號');
        $this->info('子帳號到期檢查完成');

        return 0;
    }

    private function sendExpirationNotification(array $expiringUsers)
    {
        // 這裡實作郵件發送邏輯
        Mail::send('emails.subuser-expiration', ['expiringUsers' => $expiringUsers], function ($message) {
            $message->to('yen@cheni.com.tw')
                    ->subject('子帳號即將到期通知');
        });
    }
}
