/* Crea el lote por proveedor asociado al producto de la tabla Productos Proveedores.*/
INSERT INTO lotes (id_proveedor, lote, vencimiento,  canje, vencimiento_canje, costo, usuario, fecha)
SELECT (SELECT id_proveedor FROM `productos_proveedores` pp WHERE p.id_producto=pp.id_producto LIMIT 1), CONCAT('031523','-',id_producto), '2024-12-31', 0, '0000-00-00', 0,'admin', NOW()  
FROM productos p;

/* Asigna 500 de stock por cada lote y sucursal */
INSERT INTO stock (id_producto, id_sucursal, id_lote,  stock, fraccionado)
SELECT p.id_producto, s.id_sucursal, (SELECT id_lote FROM lotes WHERE lote=CONCAT('031523','-',id_producto)), 500, 0
FROM productos p, sucursales s
ORDER BY p.id_producto, s.id_sucursal;


