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
 * @var Psr\Container\ContainerInterface $container
 * @var PDO                              $db
 * @var Johncms\Api\UserInterface        $user
 * @var Johncms\Api\ToolsInterface       $tools
 */

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

// Загрузка выбранного файла и обработка счетчика скачиваний
$error = [];
$req = $db->query("SELECT * FROM `cms_album_files` WHERE `id` = '${img}'");

if ($req->rowCount()) {
    $res = $req->fetch();

    // Проверка прав доступа
    if ($user->rights < 6 && $user->id != $res['user_id']) {
        $req_a = $db->query("SELECT * FROM `cms_album_cat` WHERE `id` = '" . $res['album_id'] . "'");

        if ($req_a->rowCount()) {
            $res_a = $req_a->fetch();
            if ($res_a['access'] == 1 || $res_a['access'] == 2 && (! isset($_SESSION['ap']) || $_SESSION['ap'] != $res_a['password'])) {
                $error[] = _t('Access forbidden');
            }
        } else {
            $error[] = _t('Wrong data');
        }
    }

    // Проверка наличия файла
    if (! $error && ! file_exists(UPLOAD_PATH . 'users/album/' . $res['user_id'] . '/' . $res['img_name'])) {
        $error[] = _t('File does not exist');
    }
} else {
    $error[] = _t('Wrong data');
}
if (! $error) {
    // Счетчик скачиваний
    if (! $db->query("SELECT COUNT(*) FROM `cms_album_downloads` WHERE `user_id` = '" . $user->id . "' AND `file_id` = '${img}'")->fetchColumn()) {
        $db->exec("INSERT INTO `cms_album_downloads` SET `user_id` = '" . $user->id . "', `file_id` = '${img}', `time` = '" . time() . "'");
        $downloads = $db->query("SELECT COUNT(*) FROM `cms_album_downloads` WHERE `file_id` = '${img}'")->fetchColumn();
        $db->exec("UPDATE `cms_album_files` SET `downloads` = '${downloads}' WHERE `id` = '${img}'");
    }
    // Отдаем файл
    header('location: ' . $config['homeurl'] . '/upload/users/album/' . $res['user_id'] . '/' . $res['img_name']);
} else {
    echo $tools->displayError($error, '<a href="./">' . _t('Back') . '</a>');
}
