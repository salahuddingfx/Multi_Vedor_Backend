<?php
$v = App\Models\ProductVariation::find(3);
if ($v && $v->price > $v->original_price) {
    $temp = $v->price;
    $v->price = $v->original_price;
    $v->original_price = $temp;
    $v->save();
    echo "Swapped ID 3!\n";
}
echo "Done.\n";
