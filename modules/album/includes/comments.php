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
 * @var Johncms\Api\UserInterface  $user
 * @var Johncms\Api\ToolsInterface $tools
 */

// Проверяем наличие комментируемого объекта
$req_obj = $db->query("SELECT * FROM `cms_album_files` WHERE `id` = '${img}'");

if ($req_obj->rowCount()) {
    $res_obj = $req_obj->fetch();

    // Получаем данные владельца Альбома
    $owner = $tools->getUser($res_obj['user_id']);

    if (! $owner) {
        echo $view->render('system::app/old_content', [
            'title'   => $textl ?? '',
            'content' => $tools->displayError(_t('User does not exists')),
        ]);
        exit;
    }

    // Показываем выбранную картинку
    unset($_SESSION['ref']);
    $res_a = $db->query('SELECT * FROM `cms_album_cat` WHERE `id` = ' . $res_obj['album_id'])->fetch();

    if (($res_a['access'] == 1 && $owner['id'] != $user->id && $user->rights < 7) || ($res_a['access'] == 2 && $user->rights < 7 && (! isset($_SESSION['ap']) || $_SESSION['ap'] != $res_a['password']) && $owner['id'] != $user->id)) {
        // Если доступ закрыт
        echo $view->render('system::app/old_content', [
            'title'   => $textl ?? '',
            'content' => $tools->displayError(_t('Access forbidden')) .
                '<div class="phdr"><a href="?act=list&amp;user=' . $owner['id'] . '">' . _t('Album List') . '</a></div>',
        ]);
        exit;
    }

    $context_top = '<div class="phdr"><a href="./"><b>' . _t('Photo Albums') . '</b></a> | ' .
        '<a href="?act=list&amp;user=' . $owner['id'] . '">' . _t('Personal') . '</a></div>' .
        '<div class="menu"><a href="?act=show&amp;al=' . $res_obj['album_id'] . '&amp;img=' . $img . '&amp;user=' . $owner['id'] . '&amp;view"><img src="../upload/users/album/' . $owner['id'] . '/' . $res_obj['tmb_name'] . '" /></a>';

    if (! empty($res_obj['description'])) {
        $context_top .= '<div class="gray">' . $tools->smilies($tools->checkout($res_obj['description'], 1)) . '</div>';
    }

    $context_top .= '<div class="sub">' .
        '<a href="../profile/?user=' . $owner['id'] . '"><b>' . $owner['name'] . '</b></a> | ' .
        '<a href="?act=show&amp;al=' . $res_a['id'] . '&amp;user=' . $owner['id'] . '">' . $tools->checkout($res_a['name']) . '</a>';

    if ($res_obj['access'] == 4 || $user->rights >= 7) {
        $context_top .= vote_photo($res_obj) .
            '<div class="gray">' . _t('Views') . ': ' . $res_obj['views'] . ', ' . _t('Downloads') . ': ' . $res_obj['downloads'] . '</div>' .
            '<a href="?act=image_download&amp;img=' . $res_obj['id'] . '">' . _t('Download') . '</a>';
    }

    $context_top .= '</div></div>';

    // Параметры комментариев
    $arg = [
        'comments_table' => 'cms_album_comments', // Таблица с комментариями
        'object_table'   => 'cms_album_files',    // Таблица комментируемых объектов
        'script'         => '?act=comments',      // Имя скрипта (с параметрами вызова)
        'sub_id_name'    => 'img',                // Имя идентификатора комментируемого объекта
        'sub_id'         => $img,                 // Идентификатор комментируемого объекта
        'owner'          => $owner['id'],         // Владелец объекта
        'owner_delete'   => true,                 // Возможность владельцу удалять комментарий
        'owner_reply'    => true,                 // Возможность владельцу отвечать на комментарий
        'owner_edit'     => false,                // Возможность владельцу редактировать комментарий
        'title'          => _t('Comments'),       // Название раздела
        'context_top'    => $context_top,         // Выводится вверху списка
        'context_bottom' => '',                   // Выводится внизу списка
    ];

    // Ставим метку прочтения
    if ($user->id == $owner['id'] && $res_obj['unread_comments']) {
        $db->exec("UPDATE `cms_album_files` SET `unread_comments` = '0' WHERE `id` = '${img}' LIMIT 1");
    }

    // Показываем комментарии
    $comm = new Johncms\Utility\Comments($arg);

    // Обрабатываем метки непрочитанных комментариев
    if ($comm->added && $user->id != $owner['id']) {
        $db->exec("UPDATE `cms_album_files` SET `unread_comments` = '1' WHERE `id` = '${img}' LIMIT 1");
    }
} else {
    echo $tools->displayError(_t('Wrong data'));
}
