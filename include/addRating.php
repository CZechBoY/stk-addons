<?php
/**
 * copyright 2011 computerfreak97
 *
 * This file is part of stkaddons
 *
 * stkaddons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * stkaddons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with stkaddons.  If not, see <http://www.gnu.org/licenses/>.
 */

session_start();
define('ROOT','../');
$security = "";
include_once('../include.php');
if (!isset($_GET['addonId']))
    die('No addon.');
if (!User::$logged_in)
    die('Not logged in.');
$addonId = mysql_real_escape_string(stripslashes($_GET['addonId']));
$rating = new Ratings($addonId);
if (isset($_GET['rating'])) {
    $rating->setUserVote($_GET['rating']);
    $new_rating = intval(stripslashes($_GET['rating']));
    echo '<div class="rating"><div class="emptystars"></div><div class="fullstars" style="width: '.$rating->getUserVotePercent().'%"></div></div>';
    exit;
}

//create the string with the number of ratings (for use in the function below)
if ($rating->getNumRatings() != 1) {
    $numRatingsString = $rating->getNumRatings().' Votes';
} else {
    $numRatingsString = $rating->getNumRatings().' Vote';
}
echo '<div class="rating"><div class="emptystars">
    </div><div class="fullstars" style="width: '.$rating->getAvgRatingPercent().'%;"></div></div><p>'.$numRatingsString.'</p>';
?>