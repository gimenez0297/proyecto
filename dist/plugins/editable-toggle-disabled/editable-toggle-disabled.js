/**
 * EditableToggleDisabled
 * version: 1.0.0
 */

/**
 * 
 * Habilitar y deshabilitar la edicion de celdas en bootstrapTable
 * 
 * @param tabla identificador de la tabla
 */
 class EditableToggleDisabled {
    constructor(tabla) {
        this.tabla = tabla;
        this.disabledCell = [];
        this.disabledCell = [];
        this.evento();
    }
    /**
     * Deshabilita las celdas al actualizar la vista de la tabla
     */
    evento() {
        $(this.tabla).on('reset-view.bs.table', () => {
            setTimeout(() => {
                for (let value of this.disabledCell) {
                    this.toggleDisabled(value, 'disable', true);
                }
            }, 1);
        });
    }
    /**
     * 
     * @param celda {index, column}
     */
    setDisabledCell(celda) {
        this.disabledCell.push(celda);
    }
    /**
     * 
     * @param celda {index, column}
     */
    removeDisabledCell(celda) {
        this.disabledCell.forEach((value, index) => {
            if (value.index == celda.index && value.column == celda.column) {
                this.disabledCell.splice(index, 1);
            }
        });
    }
    /**
     *
     * @param celda {index, column}
     * @param toggle disable / enable
     */
    toggleDisabled(celda, toggle = 'disable', resetView = false) {
        let tds = $(`${this.tabla} tbody tr[data-index='${celda.index}'] td`);
        if (typeof celda.column != 'undefined') {
            $(tds[celda.column]).children('.editable').editable(toggle);

            if (resetView === false) {
                if (toggle == 'disable') {
                    this.setDisabledCell(celda);
                } else {
                    this.removeDisabledCell(celda);
                }
            }

        } else {
            tds.children('.editable').editable(toggle);

            let celdas = tds.children();
            $.each(celdas, (index, value) => {
                if ($(value).hasClass('editable')) {
                    if (resetView === false) {
                        let c = { index: celda.index, column: index }
                        if (toggle == 'disable') {
                            this.setDisabledCell(c);
                        } else {
                            this.removeDisabledCell(c);
                        }
                    }
                }
            });
        }
    };
    /**
     *
     * @param celdas {index, column} / [{index, column}, {index, column}]
     * @param toggle disable / enable
     */
    toggleDisabledCell(celdas, toggle = 'disable') {
        if (typeof celdas.length == 'undefined') {
            this.toggleDisabled(celdas, toggle);
        } else {
            celdas.forEach(celda => {
                this.toggleDisabled(celda, toggle);
            });
        }
    };
    /**
     *
     * @param rows index de la fila o un array
     * @param toggle disable / enable
     */
    toggleDisabledRow(rows, toggle = 'disable') {
        if (typeof rows.length == 'undefined') {
            let celda = { index: rows };
            this.toggleDisabled(celda, toggle);
        } else {
            rows.forEach(row => {
                let celda = { index: row };
                this.toggleDisabled(celda, toggle);
            });
        }
    };
    /**
     *
     * @param column numero de la columna o un array
     * @param toggle disable / enable
     */
    toggleDisabledColum(column, toggle = 'disable') {
        let tableData = $(this.tabla).bootstrapTable('getData');
        if (typeof column.length == 'undefined') {
            tableData.forEach((value, index) => {
                let celda = { index, column: column };
                this.toggleDisabled(celda, toggle);
            });
        } else {
            column.forEach(value => {
                tableData.forEach((value, index) => {
                    let celda = { index, column: value };
                    this.toggleDisabled(celda, toggle);
                });
            });
        }
    };
    /**
     *
     * @param toggle disable / enable
     */
    toggleDisabledTable(toggle = 'disable') {
        let tableData = $(this.tabla).bootstrapTable('getData');
        tableData.forEach((value, index) => {
            let celda = { index };
            this.toggleDisabled(celda, toggle);
        });
    };
}