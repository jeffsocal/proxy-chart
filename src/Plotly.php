<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyChart;

use MathPHP\Statistics\KernelDensityEstimation;
use MathPHP\Statistics\Regression\LOESS;
use MathPHP\Statistics\Regression\Linear;
use ProxyChart\Fundamentals\Charts;
use Exception;

class Plotly extends Charts
{

    function __construct()
    {
        parent::__construct();
    }

    function line()
    {
        $this->sortTable($this->x_col);
        $style = array(
            "type" => "'scatter'",
            "mode" => "'lines'"
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style));
        $this->addToJsOut($json_txt);
    }

    function pointline()
    {
        $this->sortTable($this->x_col);
        $style = array(
            "type" => "'scatter'",
            "mode" => "'lines+markers'"
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style));
        $this->addToJsOut($json_txt);
    }

    function point()
    {
        $this->sortTable($this->x_col);
        $style = array(
            "type" => "'scatter'",
            "mode" => "'markers'"
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style));
        $this->addToJsOut($json_txt);
    }

    function area()
    {
        $this->sortTable($this->x_col);
        $style = array(
            "type" => "'scatter'",
            "mode" => "'lines'",
            "fill" => "'tozeroy'"
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style));
        $this->addToJsOut($json_txt);
    }

    function bar($orientation = 'v')
    {
        $this->sortTable($this->x_col);
        $this->groupby($this->x_col);
        
        $this->mungeData();
        foreach ($this->data_array as $group => $table) {
            $new_table[$this->x_col][] = "'" . $group . "'";
            $new_table['count'][] = table_length($table);
        }
        
        $this->groupby(NULL);
        
        if ($orientation == 'v') {
            $this->data($new_table, $this->x_col, 'count');
        } else {
            $this->data($new_table, 'count', $this->x_col);
        }
        
        $style = array(
            'type' => "'bar'",
            'orientation' => "'" . $orientation . "'"
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style));
        $this->addToJsOut($json_txt);
    }

    /*
     * grouped plots
     */
    function boxplot($orientation = 'v')
    {
        $this->groupby($this->x_col);
        $this->x_col = $this->y_col;
        
        $style = array(
            'type' => "'box'"
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style, true));
        
        if ($orientation == 'v')
            $json_txt = preg_replace("/x\:/", "y:", $json_txt);
        
        $this->addToJsOut($json_txt);
    }

    function histogram($orientation = 'v')
    {
        $this->y_col = $this->x_col;
        
        $style = array(
            'type' => "'histogram'"
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style, true));
        
        if ($orientation == 'h')
            $json_txt = preg_replace("/x\:/", "y:", $json_txt);
        
        $this->addToJsOut($json_txt);
    }

    function pie($hole = 0)
    {
        $this->sortTable($this->x_col);
        $this->groupby($this->x_col);
        $this->mungeData();
        foreach ($this->data_array as $group => $table) {
            $new_table[$this->x_col][] = $group;
            $new_table['count'][] = table_length($table);
        }
        
        $this->groupby(NULL);
        $this->data($new_table, 'count', $this->x_col);
        
        $style = array(
            'type' => "'pie'",
            'hole' => $hole
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style, true));
        
        $json_txt = preg_replace("/x\:/", "values:", $json_txt);
        $json_txt = preg_replace("/y\:/", "labels:", $json_txt);
        
        $this->addToJsOut($json_txt);
    }

    /*
     *
     *
     * stats plots
     *
     *
     */
    function smooth($coef = 5)
    {
        $org_table = $this->getInputTable();
        $org_x = $this->x_col;
        $org_y = $this->y_col;
        
        $this->sortTable($this->x_col);
        $this->mungeData();
        
        $table_new = array();
        foreach ($this->data_array as $group => $table) {
            
            $fat = table_keepcols($table, [
                $this->x_col,
                $this->y_col
            ]);
            $fat = table_rotate($fat);
            $x_max = array_max($table[$this->x_col]);
            $x_min = array_min($table[$this->x_col]);
            
            $x_dif = $x_max - $x_min;
            $xs = range($x_min, $x_max, $x_dif / 20);
            
            $regression = new LOESS($fat, .33, 2);
            
            $table = array();
            foreach ($xs as $x) {
                try {
                    $y = $regression->evaluate($x); // Evaluate for y at x = 5 using regression equation
                } catch (Exception $e) {
                    continue;
                }
                $table[$this->x_col][] = $x;
                $table[$this->y_col][] = $y;
            }
            $table[$this->gby_col] = array_fill(0, table_length($table), $group);
            
            if (sizeof($table_new) == 0) {
                $table_new = $table;
            } else {
                $table_new = table_bind($table_new, $table);
            }
        }
        
        $this->data($table_new, $this->x_col, $this->y_col);
        
        $style = array(
            'type' => "'scatter'",
            'mode' => "'lines'",
            'line' => [
                'shape' => "'spline'"
            ]
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style));
        $this->addToJsOut($json_txt);
        
        $this->data($org_table, $org_x, $org_y);
    }

    function regression()
    {
        $org_table = $this->getInputTable();
        $org_x = $this->x_col;
        $org_y = $this->y_col;
        
        $this->sortTable($this->x_col);
        $this->mungeData();
        
        $table_new = array();
        foreach ($this->data_array as $group => $table) {
            
            $tabr = table_keepcols($table, [
                $this->x_col,
                $this->y_col
            ]);
            $tabr = table_rotate($tabr);
            
            $regression = new Linear($tabr);
            $parameters = $regression->getParameters();
            $equation = $regression->getEquation();
            
            $x_min = array_min($table[$this->x_col]);
            $x_max = array_max($table[$this->x_col]);
            
            $table_new[$this->x_col][] = $x_min;
            $table_new[$this->x_col][] = $x_max;
            $table_new[$this->y_col][] = $regression->evaluate($x_min);
            $table_new[$this->y_col][] = $regression->evaluate($x_max);
            
            $table_new[$this->gby_col][] = $group;
            $table_new[$this->gby_col][] = $group;
        }
        
        $this->data($table_new, $this->x_col, $this->y_col);
        $this->line();
        $this->data($org_table, $org_x, $org_y);
    }

    function density($binwidth = NULL)
    {
        $org_table = $this->getInputTable();
        $org_x = $this->x_col;
        $org_y = $this->y_col;
        
        $this->sortTable($this->x_col);
        $this->mungeData();
        
        $table_new = array();
        foreach ($this->data_array as $group => $table) {
            
            $x_max = array_max($table[$this->x_col]);
            $x_min = array_min($table[$this->x_col]);
            
            $x_dif = $x_max - $x_min;
            $xs = range($x_min, $x_max, $x_dif / 20);
            
            $kde = new KernelDensityEstimation($table[$this->x_col]);
            
            $table = array();
            foreach ($xs as $x) {
                $y = $kde->evaluate($x); // Evaluate for y at x = 5 using regression equation
                if ($y == 0)
                    continue;
                $table[$this->x_col][] = $x;
                $table['density'][] = $y;
            }
            $table[$this->gby_col] = array_fill(0, table_length($table), $group);
            
            if (sizeof($table_new) == 0) {
                $table_new = $table;
            } else {
                $table_new = table_bind($table_new, $table);
            }
        }
        
        $this->data($table_new, $this->x_col, 'density');
        
        $style = array(
            'type' => "'scatter'",
            'mode' => "'lines'",
            "fill" => "'tozeroy'",
            'line' => [
                'shape' => "'spline'"
            ]
        );
        
        $json_txt = $this->arrayToJSON($this->createChart($style));
        $this->addToJsOut($json_txt);
        
        $this->data($org_table, $org_x, $org_y);
    }

    function density2d()
    {
        //
    }

    function levey_jennings()
    {
        //
    }

    function cumulative()
    {
        //
    }

    function heatmap()
    {
        //
    }
}
?>