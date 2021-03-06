<?php

declare(strict_types=1);

/*
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

namespace Library;

/**
 * Статические методы помошники
 * Class Utils
 * @package Library
 * @author  Koenig(Compolomus)
 */
class Utils
{
    /**
     * редирект на 404
     */
    public static function redir404()
    {
        /** @var \Johncms\Api\ConfigInterface $config */
        $config = \App::getContainer()->get(\Johncms\Api\ConfigInterface::class);

        ob_get_level() && ob_end_clean();
        header('Location: ' . $config['homeurl'] . '/?err');
        exit;
    }

    /**
     * Позиция символа в тексте
     * @param $text
     * @param $chr
     * @return int
     */
    public static function position($text, $chr)
    {
        $result = mb_strpos($text, $chr);

        return $result !== false ? $result : 100;
    }

    /**
     * Сортировка по рейтингу
     * @param $a
     * @param $b
     * @return int
     */
    public static function cmprang($a, $b)
    {
        if ($a['rang'] == $b['rang']) {
            return 0;
        }

        return ($a['rang'] > $b['rang']) ? -1 : 1;
    }

    /**
     * Сортировка по алфавиту
     * @param $a
     * @param $b
     * @return int
     */
    public static function cmpalpha($a, $b)
    {
        if ($a['name'] == $b['name']) {
            return 0;
        }

        return ($a['name'] < $b['name']) ? -1 : 1;
    }
}
