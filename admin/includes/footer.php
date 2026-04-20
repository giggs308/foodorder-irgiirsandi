    </div> <!-- End of main-wrapper -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');
            const body = document.body;
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                    body.classList.toggle('overflow-hidden');
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    this.classList.remove('show');
                    body.classList.remove('overflow-hidden');
                });
            }
            
            // Initialize DataTables if table has the 'datatable' class
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable();
            } else {
                $('.datatable').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    }
                });
            }
            
            // Enable tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Enable popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
        
        // Function to confirm before deleting
        function confirmDelete(event, message = 'Anda yakin ingin menghapus data ini?') {
            if (!confirm(message)) {
                event.preventDefault();
                return false;
            }
            return true;
        }
        
        // Function to show loading state on buttons
        function setButtonLoading(button, isLoading) {
            const $button = $(button);
            if (isLoading) {
                $button.prop('disabled', true);
                $button.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...`);
            } else {
                $button.prop('disabled', false);
                $button.html($button.data('original-text'));
            }
        }
        
        // Save original button text on page load
        $(document).ready(function() {
            $('button[type="submit"]').each(function() {
                $(this).data('original-text', $(this).html());
            });
            
            // Handle form submissions
            $('form').on('submit', function() {
                const $submitButton = $(this).find('button[type="submit"]');
                if ($submitButton.length) {
                    setButtonLoading($submitButton, true);
                }
            });
        });
    </script>
</body>
</html>
