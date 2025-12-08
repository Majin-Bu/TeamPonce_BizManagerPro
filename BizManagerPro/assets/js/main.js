// BizManagerPro Main JavaScript

// Run callback immediately if DOM is ready, otherwise on DOMContentLoaded
function onReady(fn) {
  if (document.readyState !== 'loading') {
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn);
  }
}

onReady(() => {
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  const bootstrapLib = window.bootstrap // Declare the bootstrap variable
  if (bootstrapLib) {
    tooltipTriggerList.map((tooltipTriggerEl) => new bootstrapLib.Tooltip(tooltipTriggerEl))
  }

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert")
  if (bootstrapLib) {
    alerts.forEach((alert) => {
      setTimeout(() => {
        const bsAlert = new bootstrapLib.Alert(alert)
        bsAlert.close()
      }, 5000)
    })
  }

  // Confirm delete actions
  const deleteButtons = document.querySelectorAll('a[onclick*="confirm"]')
  deleteButtons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      if (!confirm("Are you sure you want to delete this item?")) {
        e.preventDefault()
      }
    })
  })
})

// Format currency
function formatCurrency(value) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(value)
}

// Format date
function formatDate(date) {
  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  }).format(new Date(date))
}

// Bulk selection and delete controls for tables
onReady(function() {
  const selectAlls = Array.from(document.querySelectorAll('.select-all'));
  const rowCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));
  const bulkDeleteBtns = Array.from(document.querySelectorAll('#bulkDeleteBtn'));

  function updateBulkDeleteState() {
    const anyChecked = rowCheckboxes.some(cb => cb.checked);
    bulkDeleteBtns.forEach(btn => { if (btn) btn.disabled = !anyChecked; });
  }

  selectAlls.forEach(sa => {
    sa.addEventListener('change', function() {
      rowCheckboxes.forEach(cb => { cb.checked = sa.checked; });
      updateBulkDeleteState();
    });
  });

  rowCheckboxes.forEach(cb => {
    cb.addEventListener('change', function() {
      selectAlls.forEach(sa => {
        sa.checked = rowCheckboxes.length > 0 && rowCheckboxes.every(c => c.checked);
      });
      updateBulkDeleteState();
    });
  });

  updateBulkDeleteState();
});
