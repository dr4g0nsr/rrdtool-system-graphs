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

class rrd_update {

    private $config = NULL;   // config file, loaded

    public function __rrd_update() {
        $this->load_config();
    }

    /**
     * Load config file
     * 
     * @param string $config_file
     */
    public function load_config($config_file = false) {
        if (!$config_file) {
            $config_file = __DIR__ . "/config.php";
        }
        if (!file_exists($config_file)) {
            log:logdie("No config file");
        }
        require_once($config_file);
    }

    /**
     * Execute rrd
     * 
     * @param type $command
     */
    public function exec_rrd($command) {
        if (!$command) {
            return false;
        }
        exec("rrdtool " . $command);    // run rrdtool
    }

    /**
     * Create rrd
     * 
     */
    public function create_rrd() {
        $this->exec_rrd();
    }

}

$rrd = new rrd_update;
$rrd->create_rrd();
