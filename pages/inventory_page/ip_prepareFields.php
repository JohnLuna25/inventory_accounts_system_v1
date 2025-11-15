<?php

// This function defines 'nullIfEmpty' of the function 'prepareInventoryFields'
function nullIfEmpty($value)
{
    return ($value === '' || $value === '0') ? null : $value;
}

// This function converts empty or '0' values to null annd returns an arrray of cleaned fields.
function prepareInventoryFields($post)
{
    $stocks_in = nullIfEmpty($post['stocks_in'] ?? '');
    $stocks_out = nullIfEmpty($post['stocks_out'] ?? '');
    $description = nullIfEmpty($post['description'] ?? '');
    $batch = nullIfEmpty($post['batch'] ?? '');
    $remarks = nullIfEmpty($post['remarks'] ?? '');

    return compact('stocks_in', 'stocks_out', 'description', 'batch', 'remarks');
}

?>