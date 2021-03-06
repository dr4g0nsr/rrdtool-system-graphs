<?php

/*
 * Copyright (C) 2015 cira
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__ . "/init.php");

require_once(__DIR__ . "/update.php");

$rrd = new rrd_tools;

while (1 == 1) {
    echo PHP_EOL, "Run RRD", PHP_EOL;
    $rrd->update_rrd();
    $rrd->draw_graphs();
    $timeout = $config["update"];
    while ($timeout > 0) {
        sleep(1);
        echo $timeout, PHP_EOL;
        $timeout--;
    }
}