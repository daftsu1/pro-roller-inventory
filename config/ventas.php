<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monto por defecto para costo de instalación
    |--------------------------------------------------------------------------
    | Se usa cuando el usuario marca "Agregar instalación" y no ingresa un monto.
    | Valor en CLP. Puede sobreescribirse con VENTAS_INSTALACION_MONTO_DEFAULT en .env
    */
    'instalacion_monto_default' => (float) env('VENTAS_INSTALACION_MONTO_DEFAULT', 12500),
];
