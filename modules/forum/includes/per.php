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
    if (! $id) {
        echo $tools->displayError(_t('Wrong data'));
        echo $view->render('system::app/old_content', ['title' => $textl ?? '', 'content' => ob_get_clean()]);
        exit;
    }

    $typ = $db->query("SELECT * FROM `forum_topic` WHERE `id` = '${id}'");

    if (! $typ->rowCount()) {
        echo $tools->displayError(_t('Wrong data'));
        echo $view->render('system::app/old_content', ['title' => $textl ?? '', 'content' => ob_get_clean()]);
        exit;
    }

    if (isset($_POST['submit'])) {
        $razd = isset($_POST['razd']) ? abs((int) ($_POST['razd'])) : false;

        if (! $razd) {
            echo $tools->displayError(_t('Wrong data'));
            echo $view->render('system::app/old_content', ['title' => $textl ?? '', 'content' => ob_get_clean()]);
            exit;
        }

        $typ1 = $db->query("SELECT * FROM `forum_sections` WHERE `id` = '${razd}'");

        if (! $typ1->rowCount()) {
            echo $tools->displayError(_t('Wrong data'));
            echo $view->render('system::app/old_content', ['title' => $textl ?? '', 'content' => ob_get_clean()]);
            exit;
        }

        $db->exec("UPDATE `forum_topic` SET
            `section_id` = '${razd}'
            WHERE `id` = '${id}'
        ");
        header("Location: ?type=topic&id=${id}");
    } else {
        // Перенос темы
        $ms = $typ->fetch();

        if (empty($_GET['other'])) {
            $rz1 = $db->query("SELECT * FROM `forum_topic` WHERE id='" . $ms['section_id'] . "'")->fetch();
            $other = $ms['section_id'];
        } else {
            $other = (int) ($_GET['other']);
        }

        $fr1 = $db->query("SELECT * FROM `forum_sections` WHERE id='" . $other . "'")->fetch();
        echo '<div class="phdr"><a href="?type=topic&id=' . $id . '"><b>' . _t('Forum') . '</b></a> | ' . _t('Move Topic') . '</div>' .
            '<form action="?act=per&amp;id=' . $id . '" method="post">' .
            '<div class="gmenu"><p>' .
            '<h3>' . _t('Category') . '</h3>' . $fr1['name'] . '</p>' .
            '<p><h3>' . _t('Section') . '</h3>' .
            '<select name="razd">';
        $raz = $db->query("SELECT * FROM `forum_sections` WHERE `parent` = '" . $fr1['parent'] . "' AND section_type = 1 AND  `id` != '" . $ms['section_id'] . "' ORDER BY `sort` ASC");

        while ($raz1 = $raz->fetch()) {
            echo '<option value="' . $raz1['id'] . '">' . $raz1['name'] . '</option>';
        }

        echo '</select></p>' .
            '<p><input type="submit" name="submit" value="' . _t('Move') . '"/></p>' .
            '</div></form>' .
            '<div class="phdr">' . _t('Other categories') . '</div>';
        $frm = $db->query("SELECT * FROM `forum_sections` WHERE `id` != '${other}' AND (section_type != 1 OR section_type IS NULL) ORDER BY `sort` ASC");

        while ($frm1 = $frm->fetch()) {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            echo '<a href="?act=per&amp;id=' . $id . '&amp;other=' . $frm1['id'] . '">' . $frm1['name'] . '</a></div>';
            ++$i;
        }

        echo '<div class="phdr"><a href="./">' . _t('Back') . '</a></div>';
    }
}
