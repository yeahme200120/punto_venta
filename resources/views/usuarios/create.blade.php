{{-- Cambiar el form --}}
<form id="formCrearUsuario" onsubmit="return guardarUsuario(event)">
    @csrf
    {{-- ... mismos campos ... --}}
    <button type="submit" id="btnGuardar" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl">💾 Crear usuario</button>
</form>

<script>
async function guardarUsuario(event) {
    event.preventDefault();
    const btn = document.getElementById('btnGuardar');
    const form = document.getElementById('formCrearUsuario');
    const formData = new FormData(form);
    
    btn.disabled = true;
    Swal.fire({ title: 'Creando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const res = await axios.post(form.action, formData);
        if (res.data?.success !== false) {
            await Swal.fire({ icon: 'success', title: 'Usuario creado', timer: 2000 });
            window.location.href = '{{ route("usuarios.index") }}';
        }
    } catch(e) {
        let msg = 'Error al crear usuario';
        if (e.response?.status === 422) msg = Object.values(e.response.data.errors).flat().join('\n');
        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    } finally { btn.disabled = false; }
    return false;
}
</script>