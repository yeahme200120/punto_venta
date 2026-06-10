@if(session('swal_success') || session('swal_error') || session('swal_warning') || session('swal_info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('swal_success'))
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: "{{ addslashes(session('swal_success')) }}",
            confirmButtonText: 'Cerrar',
            timer: 3000,
            timerProgressBar: true
        });
        @endif
        
        @if(session('swal_error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ addslashes(session('swal_error')) }}",
            confirmButtonText: 'Cerrar'
        });
        @endif
        
        @if(session('swal_warning'))
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: "{{ addslashes(session('swal_warning')) }}",
            confirmButtonText: 'Cerrar'
        });
        @endif
        
        @if(session('swal_info'))
        Swal.fire({
            icon: 'info',
            title: 'Información',
            text: "{{ addslashes(session('swal_info')) }}",
            confirmButtonText: 'Cerrar'
        });
        @endif
    });
</script>
@endif