<?php

/* A function to take a date in ($date) in specified date() format (eg mm/dd/yy for 12/08/10) and 
 * return date in $outFormat (eg d.m.Y for 20.10.1208; )
 *  datum $date - Datum containing the literal date that will be modified
 *  string $outFormat - String containing the desired date output, format the same as date()
 * 
 * [CUSTOMFUNCTION|datefmt|$INVOICE_DUEDATE$|d.m.Y|CUSTOMFUNCTION] 
 */

if (!function_exists('datefmt')) {

    /**
     * @param string $date
     * @param string $outFormat
     * @return string
     * @throws Exception
     */
    function datefmt($date, $outFormat = "d.m.Y")
    {
        if ($date) {
            if (strlen($date) > 10) {
                $date = substr($date, 0, 10);
            }

            $sqlFormatDate = getValidDBInsertDateValue($date);
            $date = new DateTime($sqlFormatDate);

            return $date->format($outFormat);
        }

        return '';
    }
}
