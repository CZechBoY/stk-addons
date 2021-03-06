<?php
/**
 * copyright 2014-2015 Daniel Butum <danibutum at gmail dot com>
 *
 * This file is part of stk-addons.
 *
 * stk-addons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stk-addons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stk-addons. If not, see <http://www.gnu.org/licenses/>.
 */
require_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . "config.php");

header("Content-Type: application/json");
if (!isset($_POST["action"]) || empty($_POST["action"]))
{
    exit_json_error("action param is not defined or is empty");
}

switch ($_POST["action"])
{
    case "add-role":
        $errors = Validate::ensureNotEmpty($_POST, ["role"]);
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_PERMISSIONS))
        {
            exit_json_error("You do not have the permission to add a role");
        }

        try
        {
            AccessControl::addRole(trim($_POST["role"]));
        }
        catch(AccessControlException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success("Role added");
        break;

    case "rename-role":
        $errors = Validate::ensureNotEmpty($_POST, ["old-role", "new-role"]);
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_PERMISSIONS))
        {
            exit_json_error("You do not have the permission to rename a role");
        }

        try
        {
            AccessControl::renameRole($_POST["old-role"], trim($_POST["new-role"]));
        }
        catch(AccessControlException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success("Role renamed");
        break;

    case "delete-role":
        $errors = Validate::ensureNotEmpty($_POST, ["role"]);
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_PERMISSIONS))
        {
            exit_json_error("You do not have the permission to delete a role");
        }

        try
        {
            AccessControl::deleteRole($_POST["role"]);
        }
        catch(AccessControlException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success("Role Deleted");
        break;

    case "edit-role": // edit a role permissions or maybe the role name in the future
        $errors = Validate::ensureNotEmpty($_POST, ["role", "permissions"]);
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!is_array($_POST["permissions"]))
        {
            exit_json_error("The permissions param is not an array");
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_PERMISSIONS))
        {
            exit_json_error("You do not have the permission to change a roles permissions");
        }

        try
        {
            AccessControl::setPermissions($_POST["role"], $_POST["permissions"]);
        }
        catch(AccessControlException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success("Permissions set successfully");
        break;

    case "get-role": // get the permission of a role
        $errors = Validate::ensureNotEmpty($_POST, ["role"]);
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_PERMISSIONS))
        {
            exit_json_error("You do not have the necessary permission to get a role");
        }

        $role = $_POST["role"];
        if (!AccessControl::isRole($role))
        {
            exit_json_error("The role is not valid");
        }
        $permissions = [];
        try
        {
            $permissions = AccessControl::getPermissions($_POST["role"]);
        }
        catch(AccessControlException $e)
        {
            exit_json_error($e->getMessage());
        }


        exit_json_success("", ["permissions" => $permissions]);
        break;

    case "add-news": // add a new news entry
        $errors = array_merge(Validate::ensureNotEmpty($_POST, ["message"]), Validate::ensureIsSet($_POST, ["condition"]));
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_SETTINGS))
        {
            exit_json_error("You do not have the necessary permission to add a news entry");
        }

        $message = $_POST["message"];
        $condition = $_POST["condition"];
        $important = Util::isCheckboxChecked($_POST, "important");
        $web_display = Util::isCheckboxChecked($_POST, "web-display");

        try
        {
            News::create(User::getLoggedId(), $message, $condition, $important, $web_display);
        }
        catch(NewsException $e)
        {
            exit_json_error($e->getMessage());
        }

        writeNewsXML();
        exit_json_success("News message created");
        break;

    case "delete-news": // delete a news entry
        $errors = Validate::ensureNotEmpty($_POST, ["news-id"]);
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_SETTINGS))
        {
            exit_json_error("You do not have the necessary permission to delete a news id");
        }

        try
        {
            News::delete((int)$_POST["news-id"]);
        }
        catch(NewsException $e)
        {
            exit_json_error($e->getMessage());
        }

        writeNewsXML();
        exit_json_success("News entry deleted");
        break;

    case "edit-general-settings": // general settings update
        $errors = Validate::ensureIsSet(
            $_POST,
            [
                Config::XML_UPDATE_TIME,
                Config::ALLOWED_ADDON_EXTENSIONS,
                Config::ALLOWED_SOURCE_EXTENSIONS,
                Config::EMAIL_LIST,
                Config::EMAIL_ADMIN,
                Config::SHOW_INVISIBLE_ADDONS,
                Config::FEED_BLOG,
                Config::IMAGE_MAX_DIMENSION,
                Config::APACHE_REWRITES
            ]
        );
        if ($errors)
        {
            exit_json_error(implode("<br>", $errors));
        }
        if (!User::hasPermission(AccessControl::PERM_EDIT_SETTINGS))
        {
            exit_json_error("You do not have the necessary permission to edit general settings");
        }

        Config::set(Config::XML_UPDATE_TIME, (int)$_POST[Config::XML_UPDATE_TIME]);
        Config::set(Config::ALLOWED_ADDON_EXTENSIONS, $_POST[Config::ALLOWED_ADDON_EXTENSIONS]);
        Config::set(Config::ALLOWED_SOURCE_EXTENSIONS, $_POST[Config::ALLOWED_SOURCE_EXTENSIONS]);
        Config::set(Config::EMAIL_ADMIN, $_POST[Config::EMAIL_ADMIN]);
        Config::set(Config::EMAIL_LIST, $_POST[Config::EMAIL_LIST]);
        Config::set(Config::SHOW_INVISIBLE_ADDONS, (int)$_POST[Config::SHOW_INVISIBLE_ADDONS]);
        Config::set(Config::FEED_BLOG, $_POST[Config::FEED_BLOG]);
        Config::set(Config::IMAGE_MAX_DIMENSION, (int)$_POST[Config::IMAGE_MAX_DIMENSION]);
        Config::set(Config::APACHE_REWRITES, $_POST[Config::APACHE_REWRITES]);

        writeXML();

        exit_json_success("Settings saved");
        break;
    case "reset-ranking":
        try
        {
            Ranking::resetRanking(User::getPermissions());
        }
        catch(Exception $e)
        {
            exit_json_error($e->getMessage());
        }
        exit_json_success("Successfully reset player ranking");

        break;
    case "clear-cache": // delete all the cache
        if (!User::hasPermission(AccessControl::PERM_EDIT_SETTINGS))
        {
            exit_json_error("You do not have the necessary permission to empty the cache");
        }

        try
        {
            Cache::clear();
        }
        catch(CacheException $e)
        {
            exit_json_error($e->getMessage());
        }

        exit_json_success("Cache emptied");
        break;

    default:
        exit_json_error(sprintf("action = %s is not recognized", h($_POST["action"])));
        break;
}
