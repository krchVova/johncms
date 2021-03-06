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

require 'classes/download.php';

$textl = _t('Files on moderation');

if ($user->rights == 4 || $user->rights >= 6) {
    echo '<div class="phdr"><a href="?"><b>' . _t('Downloads') . '</b></a> | ' . $textl . '</div>';

    if ($id) {
        $db->exec("UPDATE `download__files` SET `type` = 2 WHERE `id` = '" . $id . "' LIMIT 1");
        echo '<div class="gmenu">' . _t('File accepted') . '</div>';
    } else {
        if (isset($_POST['all_mod'])) {
            $db->exec("UPDATE `download__files` SET `type` = 2 WHERE `type` = '3'");
            echo '<div class="gmenu">' . _t('All files accepted') . '</div>';
        }
    }

    $total = $db->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '3'")->fetchColumn();

    // Навигация
    if ($total > $user->config->kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('?act=mod_files&amp;', $start, $total, $user->config->kmess) . '</div>';
    }

    $i = 0;

    if ($total) {
        $req_down = $db->query("SELECT * FROM `download__files` WHERE `type` = '3' ORDER BY `time` DESC LIMIT ${start}, " . $user->config->kmess);
        while ($res_down = $req_down->fetch()) {
            echo(($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down) .
                '<div class="sub"><a href="?act=mod_files&amp;id=' . $res_down['id'] . '">' . _t('Accept') . '</a> | ' .
                '<span class="red"><a href="?act=delete_file&amp;id=' . $res_down['id'] . '">' . _t('Delete') . '</a></span></div></div>';
        }

        echo '<div class="rmenu"><form name="" action="?act=mod_files" method="post"><input type="submit" name="all_mod" value="' . _t('Accept all files') . '"/></form></div>';
    } else {
        echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
    }

    echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    // Навигация
    if ($total > $user->config->kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('?act=mod_files&amp;', $start, $total, $user->config->kmess) . '</div>' .
            '<p><form action="?" method="get">' .
            '<input type="hidden" value="top_users" name="act" />' .
            '<input type="text" name="page" size="2"/><input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
    }

    echo '<p><a href="?">' . _t('Downloads') . '</a></p>';
}

echo $view->render('system::app/old_content', ['title' => $textl ?? '', 'content' => ob_get_clean()]);
