<?php
function format_price_mdl_with_conversions($price_mdl) {
    $price_number = floatval($price_mdl);

    // Примерные курсы (обнови при необходимости)
    $rate_euro = 0.051;   // 1 MDL ≈ 0.051 €
    $rate_usd  = 0.055;   // 1 MDL ≈ 0.055 $

    $price_euro = round($price_number * $rate_euro);
    $price_usd  = round($price_number * $rate_usd);

    return "<b>{$price_number} лей</b> <div><p>/ ≈ {$price_euro} €</p> <p>/ ≈ {$price_usd} $</p></div>";
}
