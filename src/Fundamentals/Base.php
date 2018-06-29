<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */

/*
 * Plotly.js JSON SCHEMA
 *
 * data = [
 * {
 * ....type: 'scatter',.. // all "scatter" attributes: https://plot.ly/javascript/reference/#scatter
 * .... x: [1, 2, 3],.... // more about "x": #scatter-x
 * .... y: [3, 1, 6],.... // #scatter-y
 * .... marker: {........ // marker is an object, valid marker keys: #scatter-marker
 * ........ color: 'rgb(16, 32, 77)' // more about "marker.color": #scatter-marker-color
 * .... }
 * },
 * {
 * .... type: 'bar',..... // all "bar" chart attributes: #bar
 * .... x: [1, 2, 3],.... // more about "x": #bar-x
 * .... y: [3, 1, 6],.... // #bar-y
 * .... name: 'bar chart example' // #bar-name
 * }
 * ];
 *
 * layout = {.................... // all "layout" attributes: #layout
 * .... title: 'simple example',. // more about "layout.title": #layout-title
 * .... xaxis: {................. // all "layout.xaxis" attributes: #layout-xaxis
 * ........ title: 'time'........ // more about "layout.xaxis.title": #layout-xaxis-title
 * .... },
 * .... annotations: [........... // all "annotation" attributes: #layout-annotations
 * .... {
 * ........ text: 'simple annotation',... // #layout-annotations-text
 * ........ x: 0,........................ // #layout-annotations-x
 * ........ xref: 'paper',............... // #layout-annotations-xref
 * ........ y: 0,........................ // #layout-annotations-y
 * ........ yref: 'paper'................ // #layout-annotations-yref
 * .... }
 * .... ]
 * }
 */
namespace ProxyChart\Fundamentals;

class Base extends Layout
{

    //
    private $imports;

    private $table_array;

    protected $data_array;

    protected $x_col;

    protected $y_col;

    protected $z_col;

    protected $gby_col;

    protected $cby_col;

    protected $lby_col;

    private $js_output;

    //
    function __construct()
    {
        parent::__construct();
        
        $this->imports = 'js/plotly-latest.min.js';
        $this->gby_col = NULL;
    }

    //
    function getImports()
    {
        return $this->imports;
    }

    function data($table, $x, $y = NULL, $z = NULL)
    {
        $this->table_array = $table;
        
        $this->x_col = $x;
        
        $this->y_col = $y;
        
        $this->z_col = $z;
        
        $this->xtitle($x);
        
        $this->ytitle($y);
    }

    function getInputTable()
    {
        return $this->table_array;
    }

    function sortTable($column, $order = 'ascn', $type = 'number')
    {
        $this->table_array = table_sort($this->table_array, $column, $order, $type);
    }

    function groupby($variable, $function = 'array_count')
    {
        $this->gby_col = $variable;
    }

    function colorby($variable, $function = 'array_count')
    {
        $this->cby_col = $variable;
    }

    protected function colors($n)
    {
        $c = [
            'red',
            'blue',
            'orange',
            'purple',
            'yellow',
            'green'
        ];
        return array_slice($c, 0, $n);
    }

    function labels($variable)
    {
        $this->lby_col = $variable;
    }

    protected function mungeData()
    {
        $this->data_array = array();
        
        if (! is_null($this->z_col))
            $this->table_array[$this->z_col] = array_normalize(array_values($this->table_array[$this->z_col]), 50);
        
        if (! is_null($this->cby_col)) {
            $vals = array_unique($this->table_array[$this->cby_col]);
            
            $clrs = $this->colors(count($vals));
            $this->table_array['colorbyvalue'] = array_fill(0, table_length($this->table_array), 'red');
            
            foreach ($vals as $i => $val) {
                $keys = array_flip(array_keys($this->table_array[$this->cby_col], $val));
                $keys = preg_replace("/.+/", $clrs[$i], $keys);
                $this->table_array['colorbyvalue'] = array_replace($this->table_array['colorbyvalue'], $keys);
            }
        }
        
        if (! is_null($this->gby_col)) {
            $this->data_array = table_split($this->table_array, $this->gby_col);
        } else {
            $this->data_array['y~x'] = $this->table_array;
        }
        
        return $this->data_array;
    }

    function plot()
    {
        /*
         * facet plots here
         */
        $this_div = 'div_' . randomString(6, "aA");
        
        $out = $this->js_output;
        $out .= 'var data = ' . str_replace('"', '', json_encode($this->datasets)) . ';' . PHP_EOL;
        $out .= PHP_EOL;
        $out .= 'var layout = ' . str_replace('"', '', json_encode($this->getLayout())) . ';' . PHP_EOL;
        $out .= PHP_EOL;
        $out .= ' var menus = ' . str_replace('"', '', json_encode($this->getButtonMenu())) . ';' . PHP_EOL;
        $out .= PHP_EOL;
        $out .= "Plotly.newPlot('" . $this_div . "', data, layout, menus);" . PHP_EOL;
        
        $out = "	<div id=\"" . $this_div . "\" style=\"width: 100%;\">
    	</div>
    	<script>
    		" . $out . "
    	</script>";
        
        return $out;
    }

    protected function addToJsOut($str)
    {
        $this->js_output .= $str . PHP_EOL;
    }
}
?>