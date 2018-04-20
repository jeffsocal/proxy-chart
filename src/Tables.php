<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyChart;

/*
 * table-responsive
 * table-dark
 * thead-light
 * thead-dark
 * table-striped
 * table-bordered
 * table-hover
 * table-sm
 */
class Tables
{

    private $table_id;

    private $table_format;

    public function __construct($use_js = TRUE)
    {
        $this->setTableId();
    }

    private function setTableId()
    {
        $this->table_id = 'table_' . randomString(5, 'naA');
        $this->table_format = [
            'table-responsive'
        ];
    }

    public function getImports()
    {
        return 'https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js';
    }

    public function getTableJS()
    {
        return '<script>' . "
                    $(document).ready( function () {
                        $('#" . $this->table_id . "').DataTable();
                    } );
                " . '</script>' . PHP_EOL;
    }

    public function setTableStyle($option)
    {
        $available = 'table-responsive
                     table-dark
                     thead-light
                     thead-dark
                     table-striped
                     table-bordered
                     table-hover
                     table-sm';
        
        if (! strstr($available, $option))
            return '';
        
        array_push($this->table_format, $option);
    }

    public function table($table_array, $print_header = true)
    {
        if (is_false($table_array) or count($table_array) == 0)
            return '';
        
        $len = table_length($table_array);
        if ($len == 0)
            return '';
        
        $head = table_header($table_array);
        $html = "<table";
        $html .= " id=\"" . $this->table_id . "\"";
        $html .= " class=\"". array_tostring($this->table_format, ' ', '') ."\">" . PHP_EOL;
        
        /*
         * HEADER
         */
        if (is_true($print_header)) {
            $html .= "\t<thead>" . PHP_EOL;
            $html .= "\t\t<tr>" . PHP_EOL;
            foreach ($head as $col) {
                $html .= "\t\t\t<th>" . $col . "</th>" . PHP_EOL;
            }
            $html .= "\t\t</tr>" . PHP_EOL;
            $html .= "\t</thead>" . PHP_EOL;
        }
        /*
         * TABLE BODY
         */
        
        $html .= "\t<tbody>" . PHP_EOL;
        for ($i = 0; $i < $len; $i ++) {
            $html .= "\t\t<tr>" . PHP_EOL;
            foreach ($head as $col) {
                $html .= "\t\t\t<td>" . $table_array[$col][$i] . "</td>" . PHP_EOL;
            }
            $html .= "\t\t</tr>" . PHP_EOL;
        }
        $html .= "\t</tbody>" . PHP_EOL;
        $html .= "</table>" . PHP_EOL;
        return $html;
    }
}
?>