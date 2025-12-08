<?php
?>

<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <p><strong>BizManagerPro</strong> &copy; 2025. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-end">
                <p>Enterprise Business Management Platform v1.0</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/BizManagerPro/assets/js/main.js"></script>

<script>
// Initialize all dropdowns
document.addEventListener('DOMContentLoaded', function() {
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
});
</script>

<?php
?>

</body>
</html>
