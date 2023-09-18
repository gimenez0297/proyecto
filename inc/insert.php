<?php
    include ("funciones.php");

    $db = DataBase::conectar();

    for ($i=0; $i < 10000; $i++) { 
        
        $db->setQuery("INSERT INTO `gastos` (
          `id_tipo_gasto`,
          `id_sucursal`,
          `id_tipo_comprobante`,
          `id_sub_tipo_gasto`,
          `id_proveedor`,
          `id_recepcion_compra`,
          `id_carga_insumo`,
          `id_caja_chica`,
          `tipo_proveedor`,
          `nro_gasto`,
          `timbrado`,
          `fecha_emision`,
          `ruc`,
          `razon_social`,
          `condicion`,
          `fecha_vencimiento`,
          `documento`,
          `concepto`,
          `monto`,
          `gravada_10`,
          `gravada_5`,
          `exenta`,
          `imputa_iva`,
          `imputa_ire`,
          `imputa_irp`,
          `no_imputa`,
          `nro_comprobante_venta_asoc`,
          `timb_compro_venta_asoc`,
          `observacion`,
          `deducible`,
          `estado`,
          `usuario`,
          `id_cargas_premios`
        )
        VALUES
          (
            4,
            2,
            9,
            2,
            108,
            NULL,
            2,
            NULL,
            2,
            '0000150',
            '15545544',
            '2022-06-01',
            '3717611-0',
            'ACOSTA LIDER RAMON',
            1,
            NULL,
            '001-001-0000154',
            'ART.  DE LIMPIEZA',
            10000,
            10000,
            0,
            0,
            1,
            0,
            0,
            0,
            NULL,
            NULL,
            NULL,
            NULL,
            0,
            'verogonzalez',
            NULL
          );
        ");
            if (!$db->alter()) {
                echo json_encode(["status" => "error", "mensaje" => "Error de base de datos al guardar la factura"]);
                exit;
            }
    }