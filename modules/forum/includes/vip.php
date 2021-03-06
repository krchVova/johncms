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

if ($user->rights == 3 || $user->rights >= 6) {
    if (empty($_GET['id'])) {
        echo $tools->displayError(_t('Wrong data'));
        echo $view->render('system::app/old_content', ['title' => $textl ?? '', 'content' => ob_get_clean()]);
        exit;
    }

    if ($db->query("SELECT COUNT(*) FROM `forum_topic` WHERE `id` = '" . $id . "'")->fetchColumn()) {
        $db->exec("UPDATE `forum_topic` SET  `pinned` = '" . (isset($_GET['vip']) ? '1' : null) . "' WHERE `id` = '${id}'");
        header('Location: ?type=topic&id=' . $id);
    } else {
        echo $tools->displayError(_t('Wrong data'));
        echo $view->render('system::app/old_content', ['title' => $textl ?? '', 'content' => ob_get_clean()]);
        exit;
    }
}
