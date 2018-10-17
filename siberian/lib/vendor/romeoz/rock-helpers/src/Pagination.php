<?php

namespace rock\helpers;

use rock\base\ClassName;

/**
 * Helper "Pagination"
 *
 * @package rock\helpers
 */
class Pagination
{
    use ClassName;

    const SORT = SORT_ASC;
    const LIMIT = 10;
    const PAGE_LIMIT = 5;
    const PAGE_PARAM = 'page';

    /**
     * Get array of pages.
     *
     * @param int $count total count of items
     * @param int|null $pageCurrent
     * @param int $sort sort pages:
     *
     * - `SORT_ASC` - asc (by default)
     * - `SORT_DESC` - desc
     *
     * @param int $limit limit items
     * @param int $pageLimit limit pages
     * @return array
     */
    public static function get(
        $count,
        $pageCurrent = null,
        $limit = self::LIMIT,
        $sort = SORT_ASC,
        $pageLimit = self::PAGE_LIMIT
    )
    {
        if (empty($count)) {
            return [];
        }

        $count = (int)$count;
        $pageLimit = (int)$pageLimit;
        $pageCount = intval(($count - 1) / $limit) + 1;
        $result = [
            'pageCount' => $pageCount
        ];
        // DESC
        if ($sort === SORT_DESC) {
            static::sortDESC($result, $pageCurrent, $pageCount, $pageLimit, $limit);
            // ASC
        } else {
            static::sortASC($result, $pageCurrent, $pageCount, $pageLimit, $limit);
        }
        // Count items of more
        $result['countMore'] = $count - ($result['offset'] + $result['limit']);
        $result['countMore'] =
            $result['countMore'] >= 0
                ? $result['countMore']
                : 0;

        return $result;
    }

    protected static function sortDESC(array &$result, $pageCurrent, $pageCount, $pageLimit, $limit)
    {
        if (!isset($pageCurrent)) {
            $pageCurrent = $pageCount;
        }
        if ($pageCurrent < 1) {
            $pageCurrent = $pageCount;
        } elseif ($pageCurrent > $pageCount) {
            $pageCurrent = $pageCount;
        }
        $result['pageCurrent'] = $pageCurrent;
        // if count of pages is less, than the limit
        if ($pageCount >= $pageLimit) {
            $pageStart = $pageCurrent + floor(($pageLimit - 1) / 2);
            if ($pageStart > $pageCount) {
                $pageStart = $pageCount;
            }
            $pageEnd = $pageStart - $pageLimit + 1;
            if ($pageEnd <= 1) {
                $pageStart = $pageLimit;
                $pageEnd = 1;
            }
        } else {
            $pageStart = $pageCount;
            $pageEnd = 1;
        }
        $result['pageStart'] = (int)$pageStart;
        $result['pageEnd'] = (int)$pageEnd;
        for ($i = $pageStart; $i >= $pageEnd; --$i) {
            $result['pageDisplay'][] = $i;
            if ($i === $result['pageCurrent']) {
                if (!(($i + 1) > $pageStart)) {
                    $result['pagePrev'] = $i + 1;
                }
                if (!(($i - 1) < $pageEnd)) {
                    $result['pageNext'] = $i - 1;
                }
            }
        }
        // page first number
        if ($pageCurrent < $pageCount) {
            $result['pageFirst'] = $pageCount;
        } else {
            $result['pageFirst'] = null;
        }
        // page last number
        if ($pageCurrent > 1) {
            $result['pageLast'] = 1;
        } else {
            $result['pageLast'] = null;
        }
        $result['offset'] = ($pageCount - $pageCurrent) * $limit;
        $result['limit'] = $limit;
    }

    protected static function sortASC(array &$result, $pageCurrent, $pageCount, $pageLimit, $limit)
    {
        if (!isset($pageCurrent)) {
            $pageCurrent = 1;
        }
        if ($pageCurrent < 1) {
            $pageCurrent = 1;
        } elseif ($pageCurrent > $pageCount) {
            $pageCurrent = $pageCount;
        }
        $result['pageCurrent'] = $pageCurrent;
        // if count of pages is less, than the limit
        if ($pageCount >= $pageLimit) {
            $pageStart = $pageCurrent - floor(($pageLimit - 1) / 2);
            if ($pageStart < 1) {
                $pageStart = 1;
            }
            $pageEnd = $pageStart + $pageLimit - 1;
            if ($pageEnd > $pageCount) {
                $pageEnd = $pageCount;
                $pageStart = $pageEnd - $pageLimit + 1;
            }
        } else {
            $pageStart = 1;
            $pageEnd = $pageCount;
        }
        $result['pageStart'] = (int)$pageStart;
        $result['pageEnd'] = (int)$pageEnd;
        for ($i = $pageStart; $i <= $pageEnd; ++$i) {
            $result['pageDisplay'][] = $i;
            if ($i === $result['pageCurrent']) {
                if (!(($i - 1) < $pageStart)) {
                    $result['pagePrev'] = $i - 1;
                }
                if (!(($i + 1) > $pageEnd)) {
                    $result['pageNext'] = $i + 1;
                }
            }
        }
        // page first number
        if ($pageCurrent > 1) {
            $result['pageFirst'] = 1;
        } else {
            $result['pageFirst'] = null;
        }
        // page last number
        if ($pageCurrent < $pageCount) {
            $result['pageLast'] = $pageCount;
        } else {
            $result['pageLast'] = null;
        }
        $result['offset'] = ($pageCurrent - 1) * $limit;
        $result['limit'] = $limit;
    }
}