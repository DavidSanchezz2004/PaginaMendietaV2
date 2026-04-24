/**
 * compras-accounting.js
 * Gestiona el modal de completado contable para el módulo de Compras.
 * Requiere: window.ComprasRoutes con accountingGet(id) e accountingSave(id)
 */

(function () {
    "use strict";

    /* ──────────────────────────────────────────────
     * DOM refs
     * ─────────────────────────────────────────────*/
    var overlay = null;
    var form = null;
    var submitBtn = null;
    var submitIcon = null;
    var currentId = null; // purchase id being edited

    function getEl(id) {
        return document.getElementById(id);
    }

    /* ──────────────────────────────────────────────
     * Init (called once DOM is ready)
     * ─────────────────────────────────────────────*/
    function init() {
        overlay = getEl("accounting-modal-overlay");
        form = getEl("am-form");
        submitBtn = getEl("am-submit");
        submitIcon = getEl("am-submit-icon");

        if (!overlay || !form) return;

        // Close on overlay click / cancel btn / close btn
        overlay.addEventListener("click", function (e) {
            if (e.target === overlay) closeModal();
        });

        var btnClose = getEl("am-close");
        var btnCancel = getEl("am-cancel");
        if (btnClose) btnClose.addEventListener("click", closeModal);
        if (btnCancel) btnCancel.addEventListener("click", closeModal);

        // Escape key
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && isOpen()) closeModal();
        });

        // Form submit
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            saveAccounting();
        });
    }

    function isOpen() {
        return overlay && overlay.style.display !== "none";
    }

    /* ──────────────────────────────────────────────
     * Open modal
     * ─────────────────────────────────────────────*/
    window.openAccountingModal = function (purchaseId) {
        if (!overlay) {
            init();
        }
        if (!overlay) return;

        currentId = purchaseId;
        resetForm();
        overlay.style.display = "block";

        getEl("am-missing-summary").textContent = "Cargando datos…";

        var url = window.ComprasRoutes.accountingGet(purchaseId);

        fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        })
            .then(function (r) {
                if (!r.ok) throw new Error("Error " + r.status);
                return r.json();
            })
            .then(function (data) {
                populateModal(data);
            })
            .catch(function (err) {
                getEl("am-missing-summary").textContent =
                    "Error al cargar datos. " + err.message;
            });
    };

    /* ──────────────────────────────────────────────
     * Close modal
     * ─────────────────────────────────────────────*/
    function closeModal() {
        if (overlay) overlay.style.display = "none";
        currentId = null;
    }

    /* ──────────────────────────────────────────────
     * Reset form to blank state
     * ─────────────────────────────────────────────*/
    function resetForm() {
        if (!form) return;
        form.reset();
        toggleCuotasModal("1");

        // Hide autofill notice
        var notice = getEl("am-autofill-notice");
        if (notice) notice.style.display = "none";

        // Clear read-only display fields
        [
            "am-view-tipo",
            "am-view-serie",
            "am-view-proveedor",
            "am-view-total",
            "am-view-fecha",
        ].forEach(function (id) {
            var el = getEl(id);
            if (el) el.textContent = "";
        });

        // Clear error states
        form.querySelectorAll(".am-field--error").forEach(function (el) {
            el.classList.remove("am-field--error");
        });
    }

    /* ──────────────────────────────────────────────
     * Populate the modal with API data
     * ─────────────────────────────────────────────*/
    function populateModal(data) {
        var purchase = data.purchase || {};
        var suggestions = data.suggestions || {};
        var missing = data.missing || [];

        // Read-only display
        setTextContent(
            "am-view-tipo",
            purchase.tipo_documento_label ||
                purchase.codigo_tipo_documento ||
                "—",
        );
        setTextContent("am-view-serie", purchase.serie_numero || "—");
        setTextContent("am-view-proveedor", purchase.proveedor_nombre || "—");
        setTextContent(
            "am-view-total",
            formatMoney(purchase.monto_total, purchase.codigo_moneda),
        );
        setTextContent("am-view-fecha", purchase.fecha_emision || "—");

        // Missing summary
        var summary = getEl("am-missing-summary");
        if (summary) {
            if (missing.length === 0) {
                summary.textContent =
                    "Todos los campos contables están completos.";
                summary.style.color = "#059669";
            } else {
                summary.textContent =
                    missing.length +
                    " campo(s) pendiente(s): " +
                    missing.join(", ");
                summary.style.color = "#d97706";
            }
        }

        // Editable fields — use saved value first, then suggestion
        setValue(
            "am-tipo-operacion",
            pick(purchase.tipo_operacion, suggestions.tipo_operacion),
        );
        setValue(
            "am-tipo-compra",
            pick(purchase.tipo_compra, suggestions.tipo_compra),
        );
        setValue(
            "am-cuenta-contable",
            pick(purchase.cuenta_contable, suggestions.cuenta_contable),
        );
        setValue("am-codigo-ps", purchase.codigo_producto_servicio || "");
        var fpRaw = pick(purchase.forma_pago, suggestions.forma_pago);
        if (fpRaw && fpRaw.length === 1) fpRaw = "0" + fpRaw;
        setValue("am-forma-pago", fpRaw);
        setValue("am-glosa", purchase.glosa || "");
        setValue("am-centro-costo", purchase.centro_costo || "");
        setValue("am-tipo-gasto", purchase.tipo_gasto || "");
        setValue("am-sucursal", purchase.sucursal || "");
        setValue("am-comprador", purchase.comprador || "");

        // Checkboxes
        setChecked("am-es-anticipo", purchase.es_anticipo);
        setChecked("am-es-contingencia", purchase.es_documento_contingencia);
        setChecked("am-es-detraccion", purchase.es_sujeto_detraccion);
        setChecked("am-es-retencion", purchase.es_sujeto_retencion);
        setChecked("am-es-percepcion", purchase.es_sujeto_percepcion);

        // Cuotas
        var cuotas = purchase.lista_cuotas || [];
        if (cuotas.length > 0) {
            setValue("am-cuota1-fecha", cuotas[0] ? cuotas[0].fecha || "" : "");
            setValue("am-cuota1-monto", cuotas[0] ? cuotas[0].monto || "" : "");
        }
        if (cuotas.length > 1) {
            setValue("am-cuota2-fecha", cuotas[1] ? cuotas[1].fecha || "" : "");
            setValue("am-cuota2-monto", cuotas[1] ? cuotas[1].monto || "" : "");
        }

        // Show cuotas section if forma_pago is credit
        var formaPago = getEl("am-forma-pago");
        if (formaPago) toggleCuotasModal(formaPago.value);

        // Show autofill notice if we used any suggestion
        var usedSuggestion = false;
        [
            "tipo_operacion",
            "tipo_compra",
            "cuenta_contable",
            "forma_pago",
        ].forEach(function (k) {
            if (!purchase[k] && suggestions[k]) usedSuggestion = true;
        });
        var notice = getEl("am-autofill-notice");
        if (notice && usedSuggestion) notice.style.display = "flex";

        // Highlight missing fields
        missing.forEach(function (field) {
            var el = form.querySelector('[data-field="' + field + '"]');
            if (el) el.classList.add("am-field--error");
        });
    }

    /* ──────────────────────────────────────────────
     * Toggle cuotas section visibility
     * ─────────────────────────────────────────────*/
    window.toggleCuotasModal = function (val) {
        var section = getEl("am-cuotas-section");
        if (!section) return;
        // '02' = crédito
        section.style.display = val === "02" || val === "2" ? "block" : "none";
    };

    /* ──────────────────────────────────────────────
     * Save accounting data
     * ─────────────────────────────────────────────*/
    function saveAccounting() {
        if (!currentId) return;

        // Build payload
        var data = {
            tipo_operacion: getVal("am-tipo-operacion"),
            tipo_compra: getVal("am-tipo-compra"),
            cuenta_contable: getVal("am-cuenta-contable"),
            codigo_producto_servicio: getVal("am-codigo-ps"),
            forma_pago: getVal("am-forma-pago"),
            glosa: getVal("am-glosa"),
            centro_costo: getVal("am-centro-costo"),
            tipo_gasto: getVal("am-tipo-gasto"),
            sucursal: getVal("am-sucursal"),
            comprador: getVal("am-comprador"),
            es_anticipo: isChecked("am-es-anticipo"),
            es_documento_contingencia: isChecked("am-es-contingencia"),
            es_sujeto_detraccion: isChecked("am-es-detraccion"),
            es_sujeto_retencion: isChecked("am-es-retencion"),
            es_sujeto_percepcion: isChecked("am-es-percepcion"),
        };

        // Cuotas (only when crédito)
        if (data.forma_pago === "02" || data.forma_pago === "2") {
            data.cuota_1_fecha = getVal("am-cuota1-fecha");
            data.cuota_1_monto = getVal("am-cuota1-monto");
            data.cuota_2_fecha = getVal("am-cuota2-fecha");
            data.cuota_2_monto = getVal("am-cuota2-monto");
        }

        // UI — loading state
        setSubmitLoading(true);

        // Clear previous errors
        form.querySelectorAll(".am-field--error").forEach(function (el) {
            el.classList.remove("am-field--error");
        });

        var url = window.ComprasRoutes.accountingSave(currentId);
        var csrfToken = getMeta("csrf-token");

        fetch(url, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(data),
        })
            .then(function (r) {
                return r.json().then(function (body) {
                    return { status: r.status, body: body };
                });
            })
            .then(function (res) {
                if (res.status === 200 || res.status === 201) {
                    handleSaveSuccess(res.body);
                } else if (res.status === 422) {
                    handleValidationErrors(res.body.errors || {});
                } else {
                    showFlash("error", res.body.message || "Error al guardar.");
                }
            })
            .catch(function (err) {
                showFlash("error", "Error de red: " + err.message);
            })
            .finally(function () {
                setSubmitLoading(false);
            });
    }

    /* ──────────────────────────────────────────────
     * Handle successful save
     * ─────────────────────────────────────────────*/
    function handleSaveSuccess(body) {
        var status =
            (body.purchase && body.purchase.accounting_status) ||
            body.accounting_status ||
            "";
        closeModal();

        // Update badge in the table row (index page) or the section header (show page)
        updateBadgeOnPage(currentId, status);

        showFlash("success", body.message || "Información contable guardada.");
        
        // Reload page after 1.5s to update all counters and stats
        setTimeout(function() {
            window.location.reload();
        }, 1500);
    }

    function updateBadgeOnPage(purchaseId, status) {
        // Update the status cell wrapper on the index page by id
        var cell = document.getElementById("status-cell-" + purchaseId);
        if (cell) {
            if (status === "listo") {
                cell.innerHTML =
                    "<span class=\"accounting-badge accounting-badge--listo\"><i class='bx bx-check-circle'></i> Listo</span>";
            } else if (status === "pendiente") {
                cell.innerHTML =
                    '<button type="button" class="btn-completar" data-purchase-id="' +
                    purchaseId +
                    "\"><i class='bx bx-edit'></i> Pendiente</button>";
                // Re-attach event
                var newBtn = cell.querySelector("[data-purchase-id]");
                if (newBtn)
                    newBtn.addEventListener("click", function () {
                        window.openAccountingModal(purchaseId);
                    });
            } else {
                cell.innerHTML =
                    '<button type="button" class="btn-completar" data-purchase-id="' +
                    purchaseId +
                    "\"><i class='bx bx-plus-circle'></i> Completar</button>";
                var newBtn2 = cell.querySelector("[data-purchase-id]");
                if (newBtn2)
                    newBtn2.addEventListener("click", function () {
                        window.openAccountingModal(purchaseId);
                    });
            }
        }

        // If we're on the show page, update the status display
        var showStatus = document.getElementById("purchase-accounting-status");
        if (showStatus) {
            showStatus.textContent = statusLabel(status);
            showStatus.className = "badge " + statusBadgeClass(status);
        }
    }

    /* ──────────────────────────────────────────────
     * Handle validation errors (422)
     * ─────────────────────────────────────────────*/
    function handleValidationErrors(errors) {
        var fieldMap = {
            tipo_operacion: "am-tipo-operacion",
            tipo_compra: "am-tipo-compra",
            cuenta_contable: "am-cuenta-contable",
            codigo_producto_servicio: "am-codigo-ps",
            forma_pago: "am-forma-pago",
        };

        Object.keys(errors).forEach(function (field) {
            var inputId = fieldMap[field];
            if (inputId) {
                var input = getEl(inputId);
                if (input && input.closest(".am-field")) {
                    input.closest(".am-field").classList.add("am-field--error");
                }
            }
        });

        var firstMsg = Object.values(errors)[0];
        if (firstMsg) {
            var summary = getEl("am-missing-summary");
            if (summary) {
                summary.textContent = Array.isArray(firstMsg)
                    ? firstMsg[0]
                    : firstMsg;
                summary.style.color = "#ef4444";
            }
        }
    }

    /* ──────────────────────────────────────────────
     * Flash message helpers
     * ─────────────────────────────────────────────*/
    function showFlash(type, message) {
        var container = document.getElementById("flash-messages");
        if (!container) return;

        var el = document.createElement("div");
        el.className =
            "alert alert-" +
            (type === "success" ? "success" : "danger") +
            " alert-dismissible fade show";
        el.setAttribute("role", "alert");
        el.innerHTML =
            escapeHtml(message) +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        container.appendChild(el);

        setTimeout(function () {
            el.remove();
        }, 6000);
    }

    /* ──────────────────────────────────────────────
     * Loading state on submit button
     * ─────────────────────────────────────────────*/
    function setSubmitLoading(loading) {
        if (!submitBtn || !submitIcon) return;
        submitBtn.disabled = loading;
        submitIcon.innerHTML = loading
            ? '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
            : "<i class='bx bx-save'></i>";
    }

    /* ──────────────────────────────────────────────
     * Utility helpers
     * ─────────────────────────────────────────────*/
    function setTextContent(id, text) {
        var el = getEl(id);
        if (el) el.textContent = text;
    }

    function setValue(id, val) {
        var el = getEl(id);
        if (!el) return;
        if (val !== undefined && val !== null) el.value = val;
    }

    function getVal(id) {
        var el = getEl(id);
        return el ? el.value : "";
    }

    function setChecked(id, val) {
        var el = getEl(id);
        if (el) el.checked = !!val;
    }

    function isChecked(id) {
        var el = getEl(id);
        return el ? el.checked : false;
    }

    function pick(a, b) {
        return a !== undefined && a !== null && a !== "" ? a : b || "";
    }

    function formatMoney(val, currency) {
        if (val === undefined || val === null) return "—";
        var sym = currency === "USD" ? "$" : currency === "EUR" ? "€" : "S/";
        return sym + " " + parseFloat(val).toFixed(2);
    }

    function getMeta(name) {
        var el = document.querySelector('meta[name="' + name + '"]');
        return el ? el.getAttribute("content") : "";
    }

    function escapeHtml(text) {
        var div = document.createElement("div");
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function statusLabel(status) {
        var map = {
            listo: "Listo",
            pendiente: "Pendiente",
            pendiente_pago: "Pend. Pago",
            no_aplica: "No Aplica",
        };
        return map[status] || status;
    }

    function statusBadgeClass(status) {
        var map = {
            listo: "badge-success",
            pendiente: "badge-warning",
            pendiente_pago: "badge-info",
            no_aplica: "badge-secondary",
        };
        return map[status] || "badge-secondary";
    }

    /* ──────────────────────────────────────────────
     * Boot
     * ─────────────────────────────────────────────*/
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
