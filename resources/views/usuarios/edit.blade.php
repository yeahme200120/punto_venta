{{-- Cambiar el form --}}
<form id="formEditarUsuario" onsubmit="return actualizarUsuario(event)">
    @csrf @method('PUT')
    {{-- ... mismos campos ... --}}
    <button type="submit" id="btnGuardarEdit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-cyan-500 text-white rounded-xl">💾 Guardar cambios</button>
</form>

<script>
async function actualizarUsuario(event) {
    event.preventDefault();
    const btn = document.getElementById('btnGuardarEdit');
    const form = document.getElementById('formEditarUsuario');
    const formData = new FormData(form);
    
    btn.disabled = true;
    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    
    try {
        const res = await axios.post(form.action, formData, { headers: { 'X-HTTP-Method-Override': 'PUT' } });
        if (res.data?.success !== false) {
            await Swal.fire({ icon: 'success', title: 'Usuario actualizado', timer: 2000 });
            window.location.href = '{{ route("usuarios.index") }}';
        }
    } catch(e) {
        let msg = 'Error al actualizar';
        if (e.response?.status === 422) msg = Object.values(e.response.data.errors).flat().join('\n');
        Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#ef4444' });
    } finally { btn.disabled = false; }
    return false;
}
</script>