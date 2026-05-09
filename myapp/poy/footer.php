        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Common UI Interactions
        $(document).ready(function() {
            // Initialize Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Auto-hide alerts
            setTimeout(function() {
                $(".alert").fadeOut('slow');
            }, 5000);

            // Dark Mode Toggle
            if (localStorage.getItem('darkMode') === 'enabled') {
                $('body').addClass('dark-mode');
                $('#darkModeToggle').prop('checked', true);
            }

            $('#darkModeToggle').on('change', function() {
                if ($(this).is(':checked')) {
                    $('body').addClass('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    $('body').removeClass('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        });
    </script>
</body>
</html>
