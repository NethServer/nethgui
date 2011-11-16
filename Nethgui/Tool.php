<?php

namespace Nethgui;

function array_head($arr)
{
    return reset($arr);
}

function array_end($arr)
{
    return end($arr);
}

function array_rest($arr)
{
    array_shift($arr);
    return $arr;
}
