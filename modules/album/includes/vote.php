<?php

declare(strict_types=1);

/*
 * This file is part of JohnCMS Content Management System.
 *
 * @copyright JohnCMS Community
 * @license   https://opensource.org/licenses/GPL-3.0 GPL-3.0
 * @link      https://johncms.com JohnCMS Project
 */

defined('_IN_JOHNCMS') || die('Error: restricted access');

/**
 * @var PDO                        $db
 * @var Johncms\Api\ToolsInterface $tools
 * @var Johncms\Api\UserInterface  $user
 */

$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';
$ref = isset($_SERVER['HTTP_REFERER']) && ! empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : './';

// Голосуем за фотографию
if (! $img) {
    echo $tools->displayError(_t('Wrong data'));
    echo $view->render('system::app/old_content', [
        'title'   => $textl ?? '',
        'content' => ob_get_clean(),
    ]);
    exit;
}

$check = $db->query("SELECT * FROM `cms_album_votes` WHERE `user_id` = '" . $user->id . "' AND `file_id` = '${img}' LIMIT 1");

if ($check->rowCount()) {
    header('Location: ' . $ref);
    exit;
}

$req = $db->query("SELECT * FROM `cms_album_files` WHERE `id` = '${img}' AND `user_id` != " . $user->id);

if ($req->rowCount()) {
    $res = $req->fetch();

    switch ($mod) {
        case 'plus':
            /**
             * Отдаем положительный голос
             */
            $db->exec("INSERT INTO `cms_album_votes` SET
                `user_id` = '" . $user->id . "',
                `file_id` = '${img}',
                `vote` = '1'
            ");
            $db->exec("UPDATE `cms_album_files` SET `vote_plus` = '" . ($res['vote_plus'] + 1) . "' WHERE `id` = '${img}'");
            break;

        case 'minus':
            /**
             * Отдаем отрицательный голос
             */
            $db->exec("INSERT INTO `cms_album_votes` SET
                `user_id` = '" . $user->id . "',
                `file_id` = '${img}',
                `vote` = '-1'
            ");
            $db->exec("UPDATE `cms_album_files` SET `vote_minus` = '" . ($res['vote_minus'] + 1) . "' WHERE `id` = '${img}'");
            break;
    }

    header('Location: ' . $ref);
} else {
    echo $tools->displayError(_t('Wrong data'));
}
