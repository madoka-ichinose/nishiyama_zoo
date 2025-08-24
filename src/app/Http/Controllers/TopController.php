<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yasumi\Yasumi;
use App\Models\Contest;

class TopController extends Controller
{
    public function top()
    {
    $tz     = 'Asia/Tokyo';
    $today  = Carbon::today($tz);
    $year   = (int) $today->format('Y');

    // 年末年始休園（12/29〜1/3）
    $isYearEndClosed =
        ($today->month === 12 && $today->day >= 29) ||
        ($today->month === 1  && $today->day <= 3);

    // 日本の祝日
    $jpHolidays = Yasumi::create('Japan', $year);
    $isHolidayToday = $jpHolidays->isHoliday($today);

    // 「毎週月曜休園（ただし月曜が祝日の場合は火曜休園）」
    $isMonday  = $today->isMonday();
    $isTuesday = $today->isTuesday();

    // 月曜が祝日ならその火曜を休園扱いにする
    $yesterday = $today->copy()->subDay();
    $isYesterdayMondayHoliday = $yesterday->isMonday()
        && $jpHolidays->isHoliday($yesterday);

    $isWeeklyClosed =
        ($isMonday && !$isHolidayToday)      // 月曜 & 祝日でない → 休園
        || ($isTuesday && $isYesterdayMondayHoliday); // 月曜祝日の翌火曜 → 休園

    $isClosedToday = $isYearEndClosed || $isWeeklyClosed;

    // 表示させたい営業時間（開園日のみ）
    $openingHours = '9:00〜16:30';

    // ===== フォトコンテスト判定 =====
        $now = Carbon::now($tz);
        $contest = Contest::active()->orderByDesc('start_at')->first();

    return view('welcome', [
        'isClosedToday' => $isClosedToday,
        'openingHours'  => $openingHours,
        'contest'       => $contest,
        // 必要なら他のデータも…
    ]);
    }
}
