<?php
foreach ( $_criteria as $k => $v )
{
    echo "$k = ".$_criteria[$k]->get_criteria_value("VALUE", false)."<BR>";
}
?>
