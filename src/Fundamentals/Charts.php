<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyChart\Fundamentals;

class Charts extends Base
{

    function __construct()
    {
        parent::__construct();
    }

    /*
     * multiAxis plots
     */
    protected function createChart($style = NULL, $singleAxis = FALSE)
    {
        $array_out = array();
        
        if (is_null($style)) {
            $style = array(
                'type' => '"scatter"'
            );
        }
        
        $this->mungeData();
        $n_groups = sizeof($this->data_array);
        foreach ($this->data_array as $group => $table) {
            
            $this_ds = 'dataset_' . randomString(3, 'A');
            
            $this->addToDatasets($this_ds);
            
            if (is_true($this->str_xval)) {
                $table[$this->x_col] = preg_filter('/^/', "'", $table[$this->x_col]);
                $table[$this->x_col] = preg_filter('/$/', "'", $table[$this->x_col]);
            }
            $array_out[$this_ds]['x'] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', array_values($table[$this->x_col]));
            
            if (! is_null($this->y_col) && is_false($singleAxis)) {
                if (is_true($this->str_yval)) {
                    $table[$this->y_col] = preg_filter('/$/', "'", $table[$this->y_col]);
                    $table[$this->y_col] = preg_filter('/^/', "'", $table[$this->y_col]);
                }
                $array_out[$this_ds]['y'] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', array_values($table[$this->y_col]));
            }
            
            // if ($n_groups > 1)
            $array_out[$this_ds]['name'] = "'" . $group . "'";
            
            if (! is_null($this->lby_col))
                $array_out[$this_ds]['text'] = "[" . array_tostring(array_values($table[$this->lby_col]), ",", "'") . "]";
            
            $array_out[$this_ds] = array_merge($array_out[$this_ds], $style);
            
            if (! is_null($this->z_col))
                $array_out[$this_ds]['marker']['size'] = array_values($table[$this->z_col]);
            
            if (! is_null($this->cby_col))
                $array_out[$this_ds]['marker']['color'] = "[" . array_tostring(array_values($table['colorbyvalue']), ",", "'") . "]";
        }
        
        return $array_out;
    }

    function arrayToJSON($arr_plot)
    {
        $json_txt = PHP_EOL;
        foreach ($arr_plot as $plot_name => $plot_data) {
            
            $json_txt .= 'var ' . $plot_name . ' = ';
            $json_txt .= json_encode($plot_data, JSON_NUMERIC_CHECK) . ";" . PHP_EOL;
            $json_txt .= PHP_EOL;
        }
        $json_txt = preg_replace("/\"/", '', $json_txt);
        $json_txt = preg_replace("/\'/", '"', $json_txt);
        
        return $json_txt;
    }
}
?>