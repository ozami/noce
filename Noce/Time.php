<?php
namespace Noce;

class Time
{
    public static function timestamp($time)
    {
        if (is_int($time)) {
            return $time;
        }
        if (is_float($time)) {
            return (int) $time;
        }
        if (preg_match("/^[0-9]+$/", $time)) {
            return (int) $time;
        }
        if ($time instanceof \DateTime) {
            return $time->getTimestamp();
        }
        $ts = strtotime($time);
        if ($ts === false) {
            throw new \RuntimeException();
        }
        return $ts;
    }

    public static function format($time, $format)
    {
        $time = self::timestamp($time);
        return date($format, $time);
    }

    public static function date($time)
    {
        return self::format($time, "Y-m-d");
    }

    public static function year($time)
    {
        $date = getdate(self::timestamp($time));
        return $date["year"];
    }

    public static function month($time)
    {
        $date = getdate(self::timestamp($time));
        return $date["mon"];
    }

    public static function day($time)
    {
        $date = getdate(self::timestamp($time));
        return $date["mday"];
    }

    public static function hours($time)
    {
        $date = getdate(self::timestamp($time));
        return $date["hours"];
    }

    public static function minutes($time)
    {
        $date = getdate(self::timestamp($time));
        return $date["minutes"];
    }

    public static function weekday($time)
    {
        $date = getdate(self::timestamp($time));
        return $date["wday"];
    }

    public static function daysInMonth($time)
    {
        $time = self::timestamp($time);
        extract(getdate($time));
        extract(getdate(mktime(0, 0, 0, $mon, 32, $year)));
        return 32 - $mday;
    }

    /**
     * 時刻を加算・減算する
     *
     * mktime() と同様に日付の有効性を考慮して計算する。
     * <pre>
     * [使用例]
     * $today = mktime(0, 0, 0, 2, 29, 2004); // 2004.2.29
     * $next_year = Time::offset(array("year" => 1));
     * // $next_year は 2005.3.1 になる（2005.2.28 は存在しないため、その翌日になる）。
     * </pre>
     *
     * @param int $time   基準となる時刻。time() の返値と同じ形式。
     * @param array $offset   加減する値。getdate() の返り値と同じ形式。
     *
     * @return integer    加減した時刻。
     */
    public static function offset($time, array $offset)
    {
        $time = self::timestamp($time);
        extract(getdate($time));
        return mktime(
            $hours + @$offset["hour"],
            $minutes + @$offset["minute"],
            $seconds + @$offset["second"],
            $mon + @$offset["month"],
            $mday + @$offset["day"],
            $year + @$offset["year"]);
    }

    /**
     * 時刻を指定した単位に丸める
     *
     * <pre>
     * [使用例]
     * $now        = time();
     * $today      = Time::truncate($now, "day");
     * $this_month = Time::truncate($now, "month");
     * $this_year  = Time::truncate($now, "year");
     * </pre>
     *
     * @param integer $time   基準となる時刻。time() の返値と同じ形式。
     * @param string  $field  丸める単位。以下のいずれかの値を指定する：
     * <pre>
     * "year"    年
     * "month"   月
     * "day"     日
     * "hour"    時
     * "minute"  分
     * </pre>
     *
     * @return int  丸めた時刻
     */
    public static function truncate($time, $field)
    {
        $time = self::timestamp($time);
        extract(getdate($time));
        switch ($field) {
            case "year":   $mon     = 1;
            case "month":  $mday    = 1;
            case "day":    $hours   = 0;
            case "hour":   $minutes = 0;
            case "minute": $seconds = 0;
            break;

            case "week":
            $mday -= $wday;
            break;

            default:
            throw new \RuntimeException();
        }
        return mktime($hours, $minutes, $seconds, $mon, $mday, $year);
    }
    
    public static function today()
    {
        return Time::truncate(time(), "day");
    }

    public static function age($birthday)
    {
        $birthday = date("Ymd", self::timestamp($birthday));
        $now = date("Ymd");
        return floor(($now - $birthday) / 10000);
    }
}
