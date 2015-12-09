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

// configs
$config = [
    "title" => "RRD tool page", // title of the page
    "server" => "server1",  // server name
    "update" => 300,    // how many sec between updates
    "monitor_disk" => ["sda", "sdb"],   // hard disk names to monitor
    "monitor_network" => ["eth0"],  // ethernet nic to monitor
    "monitor_apache"=>[],   // apache
];
