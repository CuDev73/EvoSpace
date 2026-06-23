// ==========================================================
// secciones/pagos.js - Todas las funciones de pagos.php
// ==========================================================

// ========== SELECCIONAR CONCEPTO ==========
function seleccionarConcepto(concepto) {
    const url = new URL(window.location.href);
    url.searchParams.set('concepto', concepto);
    window.location.href = url.toString();
}

// ========== CARGAR ALUMNO EN MODAL ==========
function cargarAlumno(alumno) {
    document.getElementById('pago_id_alumno').value = alumno.id_alumno;
    document.getElementById('pago_id_curso').value = alumno.id_curso;
    document.getElementById('pago_alumno_nombre').value = alumno.nombre + ' ' + alumno.apellido;
    document.getElementById('pago_curso').value = alumno.curso_tipo + ' - ' + alumno.curso_nombre;

    fetch('obtener_precios.php?id_curso=' + alumno.id_curso)
        .then(response => response.json())
        .then(data => {
            const selectConcepto = document.getElementById('pago_concepto');
            selectConcepto.innerHTML = '<option value="">Seleccionar concepto</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.concepto;
                option.dataset.precio = item.precio;
                option.dataset.descuento = item.descuento_beca;
                option.dataset.aplicaBeca = item.aplica_beca;
                option.textContent = item.concepto.charAt(0).toUpperCase() + item.concepto.slice(1) + ' (Gs ' + item.precio + ')';
                selectConcepto.appendChild(option);
            });

            const params = new URLSearchParams(window.location.search);
            const conceptoSeleccionado = params.get('concepto') || '';
            if (conceptoSeleccionado) {
                for (let opt of selectConcepto.options) {
                    if (opt.value === conceptoSeleccionado) {
                        selectConcepto.value = conceptoSeleccionado;
                        break;
                    }
                }
                actualizarPrecio();
            }
        });

    document.getElementById('pago_fecha').value = '';
    document.getElementById('pago_cantidad').value = 1;
    document.getElementById('pago_monto').value = '';
    document.getElementById('pago_recargo').value = 0;
    document.getElementById('pago_total').value = 0;
    document.getElementById('pago_recargo_info').innerHTML = '';
    document.getElementById('pago_descuento').value = 0;
    document.getElementById('pago_beca_info').innerHTML = '';
    calcularTotal();
    document.getElementById('pago_id_alumno').setAttribute('data-becado', alumno.becado ? '1' : '0');
}

// ========== ACTUALIZAR PRECIO SUGERIDO ==========
function actualizarPrecio() {
    const selectConcepto = document.getElementById('pago_concepto');
    const selectedOption = selectConcepto.options[selectConcepto.selectedIndex];
    if (!selectedOption || !selectedOption.dataset.precio) {
        document.getElementById('pago_monto').value = '';
        return;
    }
    const precio = parseFloat(selectedOption.dataset.precio) || 0;
    document.getElementById('pago_monto').value = precio;

    const porcentajeBeca = parseFloat(document.getElementById('porcentaje_beca_global').value) || 45.45;
    const esBecado = document.getElementById('pago_id_alumno').getAttribute('data-becado') === '1';
    const esCuota = selectedOption.value.toLowerCase() === 'cuota';

    if (esCuota && esBecado) {
        const descuentoAplicado = 100 - porcentajeBeca;
        document.getElementById('pago_descuento').value = descuentoAplicado;
        document.getElementById('pago_beca_info').innerHTML =
            '<i class="bi bi-info-circle-fill text-warning"></i> Beca: paga el ' + porcentajeBeca + '% de la cuota (descuento del ' + descuentoAplicado + '%)';
    } else {
        document.getElementById('pago_descuento').value = 0;
        document.getElementById('pago_beca_info').innerHTML = '';
    }
    calcularTotal();
}

// ========== VER PAGOS (AJAX) ==========
function verPagos(idAlumno) {
    const modal = new bootstrap.Modal(document.getElementById('modalVerPagos'));
    modal.show();

    document.getElementById('contenidoVerPagos').innerHTML = `
        <div class="modal-body text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;

    fetch('obtener_pagos.php?id_alumno=' + idAlumno)
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar los pagos');
            return response.text();
        })
        .then(html => {
            document.getElementById('contenidoVerPagos').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('contenidoVerPagos').innerHTML = `
                <div class="modal-body">
                    <div class="alert alert-danger">Error: ${error.message}</div>
                </div>
            `;
        });
}

// ========== CÁLCULOS ==========
function calcularRecargo() {
    const fecha = document.getElementById('pago_fecha').value;
    if (!fecha) return;
    const dia = new Date(fecha).getDate();
    let recargo = 0;
    if (dia > 10) {
        recargo = (dia - 10) * 1000;
    }
    document.getElementById('pago_recargo').value = recargo;
    if (recargo > 0) {
        document.getElementById('pago_recargo_info').innerHTML = '<i class="bi bi-exclamation-triangle-fill text-danger"></i> Recargo por atraso: ' + recargo + ' Gs (' + (dia - 10) + ' días)';
    } else {
        document.getElementById('pago_recargo_info').innerHTML = '';
    }
    calcularTotal();
}

function calcularTotal() {
    const monto = parseFloat(document.getElementById('pago_monto').value) || 0;
    const cantidad = parseInt(document.getElementById('pago_cantidad').value) || 1;
    const descuento = parseFloat(document.getElementById('pago_descuento').value) || 0;
    const recargo = parseFloat(document.getElementById('pago_recargo').value) || 0;

    const subtotal = monto * cantidad;
    const descuentoAplicado = subtotal * (descuento / 100);
    const total = subtotal - descuentoAplicado + recargo;

    document.getElementById('pago_total').value = total.toFixed(2);
}

// ========== FILTRO DE BÚSQUEDA EN TIEMPO REAL ==========
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const tabla = document.getElementById('tablaAlumnos');

    if (buscador && tabla) {
        buscador.addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase();
            const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            for (let fila of filas) {
                const nombre = fila.cells[0].textContent.toLowerCase();
                fila.style.display = nombre.includes(filtro) ? '' : 'none';
            }
        });
    }
});