<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyChart;

use ProxyHTML\Tables;

class DataTables extends Tables
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getImports()
    {
        return 'https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js';
    }

    public function getTableJS()
    {
        return '<script>' . "
                    $(document).ready( function () {
                        $('#" . $this->table_id . "').DataTable({
                           \"order\": []
                        });
                    } );
                " . '</script>' . PHP_EOL;
    }
}
?>