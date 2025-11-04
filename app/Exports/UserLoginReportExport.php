<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserLoginReportExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: 會員概覽
        $sheets[] = new UserOverviewSheet($this->data);

        // 每個會員一個獨立的登入紀錄 Sheet
        foreach ($this->data['users'] as $user) {
            $sheets[] = new UserLoginDetailSheet($user, $this->data['loginLogs'][$user->id] ?? collect());
        }

        return $sheets;
    }
}

/**
 * 會員概覽工作表
 */
class UserOverviewSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data['users']->map(function ($user) {
            $loginLogs = $this->data['loginLogs'][$user->id] ?? collect();

            return [
                $user->name,
                $user->email,
                $user->login_count ?? 0,
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '尚未登入',
                $loginLogs->count(),
                $user->active ? '啟用' : '停用',
                $user->expires_at ? $user->expires_at->format('Y-m-d') : '無',
                $user->remarks ?? '無',
                $user->parentUser->name ?? '無',
            ];
        });
    }

    public function headings(): array
    {
        return [
            '會員名稱',
            'Email',
            '累計登入次數',
            '最後登入時間',
            '統計區間登入次數',
            '帳號狀態',
            '到期日',
            '備註',
            '所屬主帳號',
        ];
    }

    public function title(): string
    {
        return '會員概覽';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

/**
 * 單一會員登入紀錄工作表
 */
class UserLoginDetailSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $user;
    protected $loginLogs;

    public function __construct($user, $loginLogs)
    {
        $this->user = $user;
        $this->loginLogs = $loginLogs;
    }

    public function collection()
    {
        return $this->loginLogs->map(function ($log, $index) {
            return [
                $index + 1,
                $log->logged_in_at->format('Y-m-d H:i:s'),
                $log->logged_in_at->translatedFormat('l'), // 星期幾
                $log->ip_address ?? '-',
                $this->truncateUserAgent($log->user_agent),
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            '登入時間',
            '星期',
            'IP位址',
            '瀏覽器資訊',
        ];
    }

    public function title(): string
    {
        // Excel Sheet 名稱限制 31 字元,並清理特殊字元
        $safeName = preg_replace('/[^a-zA-Z0-9\x{4e00}-\x{9fa5}_-]/u', '_', $this->user->name);
        $name = mb_substr($safeName, 0, 25);
        return $name . '_登入紀錄';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * 截斷過長的 User Agent
     */
    private function truncateUserAgent($userAgent)
    {
        if (!$userAgent) {
            return '-';
        }

        // 簡化 User Agent 顯示
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } else {
            return mb_substr($userAgent, 0, 50);
        }
    }
}
