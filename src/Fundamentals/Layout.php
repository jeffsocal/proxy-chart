<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyChart\Fundamentals;

class Layout
{

    protected $datasets;

    protected $str_title;

    protected $str_xtitle;

    protected $str_ytitle;
    
    protected $str_xval;
    
    protected $str_yval;
    
    protected $plot_height;
    
    protected $plot_margins;

    function __construct()
    {
        $this->str_title = NULL;
        $this->str_xtitle = NULL;
        $this->str_ytitle = NULL;
        $this->str_xval = false;
        $this->str_yval = false;
        $this->plot_height = 240;
        $this->plot_margins = [
            "t" => 80,
            "b" => 40,
            "r" => 50,
            "l" => 60,
            "pad" => 5
        ];
        $this->datasets = array();
    }

    function title($str)
    {
        $this->str_title = $str;
    }

    function xtitle($str)
    {
        $this->str_xtitle = $str;
    }

    function ytitle($str)
    {
        $this->str_ytitle = $str;
    }

    function xstr($bool = true)
    {
        $this->str_xval = $bool;
    }
    
    function ystr($bool = true)
    {
        $this->str_yval = $bool;
    }
    
    protected function addToDatasets($name)
    {
        array_push($this->datasets, $name);
    }

    function height($px = 360){
        $this->plot_height = $px;
    }
    
    function margins($array){
        $this->plot_margins = $array;
    }
    
    function getLayout()
    {
        $layout["title"] = "'" . $this->str_xtitle . " ~ " . $this->str_ytitle . "'";
        
        if (! is_null($this->str_title))
            $layout["title"] = "'" . $this->str_title . "'";
        
        $layout["xaxis"] = [
            "title" => "'" . $this->str_xtitle . "'"
        ];
        $layout["yaxis"] = [
            "title" => "'" . $this->str_ytitle . "'"
        ];
        $layout["height"] = $this->plot_height;
        $layout["margin"] = $this->plot_margins;
        $layout["hovermode"] = "'closest'";
        
        return $layout;
    }
    
    function getButtonMenu()
    {
        $layout["modeBarButtonsToRemove"] = [
            "'sendDataToCloud'",
            "'lasso2d'",
            "'zoomIn2d'",
            "'zoomOut2d'",
            "'resetScale2d'",
            "'select2d'",
            "'toggleSpikelines'"
        ];
        $layout["displaylogo"] = "false";
        $layout["showTips"] = "true";
        
        return $layout;
    }
}
?>